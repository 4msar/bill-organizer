<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([UserScope::class])]
final class Bill extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
     * Get the category associated with the bill.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
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

    function markAsPaid()
    {
        $this->update(['status' => 'paid']);
    }
}
