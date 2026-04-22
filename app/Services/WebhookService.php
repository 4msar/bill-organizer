<?php

namespace App\Services;

use App\Enums\WebhookEvent;
use App\Jobs\DispatchWebhookJob;
use App\Models\Webhook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class WebhookService
{
    /**
     * Create a new webhook for the given team.
     *
     * @param  array<string, mixed>  $data
     */
    public function createWebhook(array $data): Webhook
    {
        return Webhook::create([
            'team_id' => $data['team_id'] ?? active_team_id(),
            'user_id' => $data['user_id'] ?? Auth::id(),
            'name' => $data['name'],
            'url' => $data['url'],
            'method' => $data['method'] ?? 'POST',
            'secret' => $data['secret'] ?? Str::random(40),
            'events' => $data['events'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update an existing webhook.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateWebhook(Webhook $webhook, array $data): Webhook
    {
        $webhook->update($data);

        return $webhook;
    }

    /**
     * Delete a webhook.
     */
    public function deleteWebhook(Webhook $webhook): void
    {
        $webhook->delete();
    }

    /**
     * Dispatch the webhook event to all active, subscribed webhooks for the team.
     *
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(WebhookEvent $event, int $teamId, array $payload): void
    {
        Webhook::query()
            ->where('team_id', $teamId)
            ->where('is_active', true)
            ->get()
            ->filter(fn(Webhook $webhook) => $webhook->isSubscribedTo($event))
            ->each(function (Webhook $webhook) use ($event, $payload) {
                DispatchWebhookJob::dispatch($webhook, $event, $payload);
            });
    }

    /**
     * Generate the HMAC-SHA256 signature for a payload.
     */
    public function sign(string $secret, string $payload): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }
}
