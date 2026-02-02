<?php

namespace App\Models;

use App\Enums\RecurrencePeriod;
use App\Models\Scopes\TeamScope;
use App\Observers\BillObserver;
use App\Traits\HasMetaData;
use App\Traits\HasTeam;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([TeamScope::class]), ObservedBy(BillObserver::class)]
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
        'recurrence_period' => RecurrencePeriod::class,
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        self::creating(function (Bill $bill) {
            $bill->createUniqueTags();
        });

        self::updating(function (Bill $bill) {
            $bill->createUniqueTags();
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

    /**
     * Calculate the status based on recurrence period and transactions.
     * This is used for both the accessor and the command to keep logic consistent.
     */
    public function calculateStatus(): string
    {
        $currentStatus = $this->attributes['status'] ?? 'unpaid';

        // For non-recurring bills, just return the current status
        if (! $this->is_recurring || ! $this->recurrence_period) {
            return $currentStatus;
        }

        $now = now();
        $dueDate = Carbon::parse($this->due_date);

        // Check if we're in the current period for this bill
        $isCurrentPeriod = match ($this->recurrence_period) {
            RecurrencePeriod::WEEKLY => $dueDate->isSameWeek($now),
            RecurrencePeriod::MONTHLY => $dueDate->isSameMonth($now),
            RecurrencePeriod::YEARLY => $dueDate->isSameYear($now),
            default => false,
        };

        // if the bill is in the current recurrence period
        // check for transactions to determine if it's paid or unpaid
        if ($isCurrentPeriod) {
            // In current period - check for recent transactions
            $hasTransaction = $this->transactions()
                ->whereBetween('payment_date', [
                    $dueDate->copy()->startOfPeriod($this->recurrence_period),
                    $dueDate->copy()->endOfPeriod($this->recurrence_period),
                ])
                ->exists();

            return $hasTransaction ? 'paid' : 'unpaid';
        }

        // Not in current period - check if due date is in the past
        if ($dueDate->isBefore($now)) {
            return 'overdue';
        }

        // check if due date is in the future and not in current period
        // then it's considered paid for past periods
        if ($dueDate->isAfter($now)) {
            return 'paid';
        }

        // info('Bill #' . $this->id . ' is not in the current period.');

        return $currentStatus;
    }

    /**
     * Accessor for status attribute.
     * Calculates status dynamically for recurring bills.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->calculateStatus();
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
            RecurrencePeriod::WEEKLY => $currentDueDate->addWeek()->format('Y-m-d'),
            RecurrencePeriod::MONTHLY => $currentDueDate->addMonth()->format('Y-m-d'),
            RecurrencePeriod::YEARLY => $currentDueDate->addYear()->format('Y-m-d'),
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
            ->map(fn($tag) => strtolower(trim($tag)))
            ->unique()
            ->values();
    }

    /**
     * Unique tags
     */
    public function createUniqueTags()
    {
        if ($this->tags) {
            $this->tags = array_map(
                fn($item) => strtolower(trim($item)),
                $this->tags
            );

            $this->tags = array_filter($this->tags, fn($tag) => ! empty($tag));

            $this->tags = array_values(array_unique($this->tags));
        }
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
}
