<?php

namespace App\Jobs;

use App\Enums\WebhookEvent;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

final class DispatchWebhookJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The maximum number of attempts.
     */
    public int $tries = 5;

    /**
     * Calculate the backoff in seconds between retries.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 60, 120, 300, 600];
    }

    public function __construct(
        public readonly Webhook $webhook,
        public readonly WebhookEvent $event,
        public readonly array $payload,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $body = json_encode([
            'event' => $this->event->value,
            'timestamp' => now()->toISOString(),
            'payload' => $this->payload,
        ]);

        $signature = hash_hmac('sha256', $body, $this->webhook->secret);
        $currentAttempt = $this->attempts() + 1;

        $delivery = WebhookDelivery::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event->value,
            'payload' => $this->payload,
            'status' => 'pending',
            'attempts' => $currentAttempt,
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Signature' => 'sha256='.$signature,
                    'X-Event' => $this->event->value,
                ])
                ->send('POST', $this->webhook->url, ['body' => $body]);

            $delivery->update([
                'status' => $response->successful() ? 'success' : 'failed',
                'response_status' => $response->status(),
                'response_body' => substr($response->body(), 0, 5000),
                'delivered_at' => $response->successful() ? now() : null,
            ]);

            if (! $response->successful()) {
                $this->release($this->backoff()[$this->attempts()] ?? 600);
            }
        } catch (Throwable $e) {
            $delivery->update([
                'status' => 'failed',
                'response_body' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
