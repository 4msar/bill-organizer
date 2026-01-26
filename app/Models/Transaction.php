<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
     * The attributes that should be appended to the model's array form.
     */
    protected $appends = [
        'attachment_link',
        'payment_method_name',
        'tnx_id',
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

    /**
     * Get transaction attachment link.
     */
    public function getAttachmentLinkAttribute(): ?string
    {
        if ($this->attachment) {
            return Storage::url($this->attachment);
        }

        return null;
    }

    function getPaymentMethodNameAttribute(): ?string
    {
        $methods = config('system.payment_methods');

        return $methods[$this->payment_method] ?? null;
    }

    function getTnxIdAttribute(): string
    {
        $number = date('dmy', strtotime($this->created_at));
        $number .= str_pad($this->id, 2, '0', STR_PAD_LEFT);
        $number .= str_pad($this->team_id, 2, '0', STR_PAD_LEFT);
        $number .= str_pad($this->bill_id, 2, '0', STR_PAD_LEFT);

        return 'TNX-' . $number;
    }
}
