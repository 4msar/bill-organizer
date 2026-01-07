<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use App\Traits\HasMetaData;
use App\Traits\HasTeam;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([TeamScope::class])]
final class Bill extends Model
{
    use HasFactory;

    // for check the notification are sent or not
    use HasMetaData;
    use HasTeam;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'category_id',
        'title',
        'description',
        'amount',
        'currency',
        'base_amount',
        'due_date',
        'trial_start_date',
        'trial_end_date',
        'has_trial',
        'status',
        'is_recurring',
        'recurrence_period',
        'payment_url',
        'tags',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'trial_start_date' => 'date',
        'trial_end_date' => 'date',
        'has_trial' => 'boolean',
        'is_recurring' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        self::deleted(function (Bill $bill) {
            $bill->transactions()->delete();
            $bill->notes()->detach();
        });

        self::creating(function (Bill $bill) {
            if ($bill->tags) {
                $bill->tags = array_map(fn ($item) => strtolower(trim($item)), $bill->tags);
                $bill->tags = array_filter($bill->tags, fn ($tag) => ! empty($tag));
            }
        });
    }

    /**
     * Get the user that owns the bill.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Notes associated with the bill.
     */
    public function notes()
    {
        return $this->morphToMany(Note::class, 'notable')
            ->using(Pivots\Notable::class);
    }

    /**
     * Get the transactions for the bill.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the category associated with the bill.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getStatusAttribute($value)
    {
        // If status is explicitly set, return it
        if ($value === 'paid') {
            return $value;
        }

        // Only auto-calculate status for recurring bills
        if (! $this->is_recurring || ! $this->recurrence_period) {
            return $value ?: 'unpaid';
        }

        $now = now();
        $dueDate = Carbon::parse($this->due_date);

        // Check if we're in the current period for this bill
        $isCurrentPeriod = match ($this->recurrence_period) {
            'weekly' => $dueDate->isSameWeek($now),
            'monthly' => $dueDate->isSameMonth($now),
            'yearly' => $dueDate->isSameYear($now),
            default => false,
        };

        // If we're not in the current period, check if there are transactions for this period
        if (! $isCurrentPeriod) {
            $periodStart = match ($this->recurrence_period) {
                'weekly' => $now->startOfWeek(),
                'monthly' => $now->startOfMonth(),
                'yearly' => $now->startOfYear(),
                default => null,
            };

            $periodEnd = match ($this->recurrence_period) {
                'weekly' => $now->copy()->endOfWeek(),
                'monthly' => $now->copy()->endOfMonth(),
                'yearly' => $now->copy()->endOfYear(),
                default => null,
            };

            if ($periodStart && $periodEnd) {
                $hasTransactionInPeriod = $this->transactions()
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->exists();

                return $hasTransactionInPeriod ? 'paid' : 'unpaid';
            }
        }

        return $value ?: 'unpaid';
    }

    /**
     * Calculate next due date based on recurrence period.
     */
    public function calculateNextDueDate($dueDate = null): ?string
    {
        if (! $this->is_recurring || ! $this->recurrence_period) {
            return null;
        }

        if ($dueDate) {
            return Carbon::parse($dueDate);
        }

        $currentDueDate = Carbon::parse($this->due_date);

        return match ($this->recurrence_period) {
            'weekly' => $currentDueDate->addWeek()->format('Y-m-d'),
            'monthly' => $currentDueDate->addMonth()->format('Y-m-d'),
            'yearly' => $currentDueDate->addYear()->format('Y-m-d'),
            default => null,
        };
    }

    /**
     * Check if the bill is paid.
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the due date is upcoming.
     *
     * @return bool
     */
    public function isUpcoming()
    {
        return $this->isUpcomingIn(7);
    }

    /**
     * Check if the due date is upcoming.
     *
     * @return bool
     */
    public function isUpcomingIn($days = 1)
    {
        if ($days == 'due_day') {
            $days = 0;
        }

        return $this->due_date->lte(now()->addDays(intval($days)));
    }

    /**
     * Should notify the user about the bill.
     *
     * @param  int  $days
     * @return bool
     */
    public function shouldNotify($days = 1)
    {
        if ($days == 'due_day') {
            $days = 0;
        }

        $targetDate = now()->addDays(intval($days));

        return $this->due_date->isSameDay($targetDate);
    }

    /**
     * Should notify the user about the trial end.
     *
     * @param  int  $days
     * @return bool
     */
    public function shouldNotifyTrialEnd($days = 1)
    {
        if (! $this->has_trial || ! $this->trial_end_date) {
            return false;
        }

        if ($days == 'trial_end_day') {
            $days = 0;
        }

        $targetDate = now()->addDays(intval($days));

        return $this->trial_end_date->isSameDay($targetDate);
    }

    /**
     * Check if the bill is currently in trial period.
     *
     * @return bool
     */
    public function isInTrial()
    {
        if (! $this->has_trial || ! $this->trial_start_date || ! $this->trial_end_date) {
            return false;
        }

        $now = now();

        return $now->between($this->trial_start_date, $this->trial_end_date);
    }

    /**
     * Check if the trial has ended.
     *
     * @return bool
     */
    public function isTrialEnded()
    {
        if (! $this->has_trial || ! $this->trial_end_date) {
            return false;
        }

        return now()->isAfter($this->trial_end_date);
    }

    /**
     * Get the effective due date (trial end date if in trial, otherwise regular due date).
     *
     * @return \Carbon\Carbon
     */
    public function getEffectiveDueDate()
    {
        if ($this->isInTrial()) {
            return $this->trial_end_date;
        }

        return $this->due_date;
    }

    /**
     * Scope a query to only include unpaid bills.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    /**
     * Scope a query to only include bills due in the current month.
     */
    public function scopeCurrentMonth($query)
    {
        $now = now();

        return $query->whereBetween('due_date', [
            $now->copy()->startOfMonth(),
            $now->copy()->endOfMonth(),
        ]);
    }

    /**
     * Scope a query to only include paid bills.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Get all tags
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllTags()
    {
        return self::query()
            ->whereNotNull('tags')
            ->pluck('tags')
            ->filter()
            ->flatten()
            ->map(fn ($tag) => strtolower(trim($tag)))
            ->unique()
            ->values();
    }

    /**
     * Scope a query to only include upcoming bills.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        $now = now();

        return $query->where('due_date', '>=', $now)
            ->where('due_date', '<=', $now->copy()->addDays(intval($days)))
            ->where('status', 'unpaid');
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
        ]);
    }

    /**
     * Check if the bill is already notified for a specific reminder period.
     *
     * @param  int  $daysBefore  Number of days before the due date
     * @param  array  $channels  Notification channels (e.g., ['mail', 'database'])
     */
    public function isAlreadyNotified($daysBefore, array $channels = []): bool
    {
        foreach ($channels as $channel) {
            $data = $this->getMeta("{$channel}_notification", []);

            if (is_array($data) && array_key_exists("notified:$daysBefore", $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark the bill as notified for a specific reminder period.
     *
     * @param  int  $daysBefore  Number of days before the due date
     * @param  array  $channels  Notification channels (e.g., ['mail', 'database'])
     */
    public function markAsNotified($daysBefore, array $channels = []): void
    {
        foreach ($channels as $channel) {
            $previousData = $this->getMeta($channel, []);

            $this->setMeta("{$channel}_notification", array_merge($previousData, [
                "notified:$daysBefore" => now()->toDateTimeString(),
            ]));
        }
    }

    /**
     * Check if the bill trial end is already notified for a specific reminder period.
     *
     * @param  int  $daysBefore  Number of days before the trial end date
     * @param  array  $channels  Notification channels (e.g., ['mail', 'database'])
     */
    public function isAlreadyNotifiedTrialEnd($daysBefore, array $channels = []): bool
    {
        foreach ($channels as $channel) {
            $data = $this->getMeta("{$channel}_trial_notification", []);

            if (is_array($data) && array_key_exists("trial_notified:$daysBefore", $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark the bill trial end as notified for a specific reminder period.
     *
     * @param  int  $daysBefore  Number of days before the trial end date
     * @param  array  $channels  Notification channels (e.g., ['mail', 'database'])
     */
    public function markAsNotifiedTrialEnd($daysBefore, array $channels = []): void
    {
        foreach ($channels as $channel) {
            $previousData = $this->getMeta($channel, []);

            $this->setMeta("{$channel}_trial_notification", array_merge($previousData, [
                "trial_notified:$daysBefore" => now()->toDateTimeString(),
            ]));
        }
    }

    /**
     * Get the effective currency for the bill.
     * Falls back to team's base currency if not set.
     */
    public function getEffectiveCurrency(): string
    {
        if ($this->currency) {
            return $this->currency;
        }

        return $this->team?->currency ?? 'USD';
    }

    /**
     * Get the effective amount for display.
     * Returns the amount in the bill's currency.
     */
    public function getDisplayAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get the base amount for reporting.
     * Falls back to regular amount if not converted.
     */
    public function getBaseAmount(): float
    {
        return $this->base_amount ?? $this->amount;
    }
}
