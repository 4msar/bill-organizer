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
    use HasTeam;
    // for check the notification are sent or not
    use HasMetaData;

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
                $bill->tags = array_map(fn($item) => strtolower(trim($item)), $bill->tags);
                $bill->tags = array_filter($bill->tags, fn($tag) => !empty($tag));
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

    /**
     * Calculate next due date based on recurrence period.
     */
    public function calculateNextDueDate(): ?string
    {
        if (! $this->is_recurring || ! $this->recurrence_period) {
            return null;
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
     * @param integer $days
     * @return boolean
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
     * Scope a query to only include unpaid bills.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
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
     * @param int $daysBefore Number of days before the due date
     * @param array $channels Notification channels (e.g., ['mail', 'database'])
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
     * @param int $daysBefore Number of days before the due date
     * @param array $channels Notification channels (e.g., ['mail', 'database'])
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
}
