<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy(TeamScope::class)]
final class Transaction extends Model
{
    use HasFactory;
    use HasTeam;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'bill_id',
        'amount',
        'payment_date',
        'payment_method',
        'attachment',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that made the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bill associated with the transaction.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
