<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([TeamScope::class])]
final class Bill extends Model
{
    use HasFactory;

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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the user that owns the bill.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
        return $this->due_date->lte(now()->addDays($days)) &&
            $this->status === 'unpaid';
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
     * Scope a query to only include upcoming bills.
     */
    public function scopeUpcoming($query, $days = 7)
    {
        $now = now();

        return $query->where('due_date', '>=', $now)
            ->where('due_date', '<=', $now->copy()->addDays($days))
            ->where('status', 'unpaid');
    }

    public function markAsPaid()
    {
        $this->update(['status' => 'paid']);
    }
}
