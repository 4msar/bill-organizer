<?php

namespace App\Models;

use App\Enums\WebhookEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WebhookDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'status',
        'response_status',
        'response_body',
        'attempts',
        'delivered_at',
    ];

    protected $appends = ['event_name'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'response_status' => 'integer',
            'delivered_at' => 'datetime',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function getEventNameAttribute(): string
    {
        $eventName = WebhookEvent::from($this->event)->label();

        return $eventName ?? $this->event;
    }
}
