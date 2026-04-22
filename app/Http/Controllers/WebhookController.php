<?php

namespace App\Http\Controllers;

use App\Enums\WebhookEvent;
use App\Http\Requests\Webhook\StoreWebhookRequest;
use App\Http\Requests\Webhook\UpdateWebhookRequest;
use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;

final class WebhookController extends Controller
{
    /**
     * Store a newly created webhook.
     */
    public function store(StoreWebhookRequest $request, WebhookService $webhookService): RedirectResponse
    {
        $webhookService->createWebhook($request->validated());

        return back()->with('success', 'Webhook created successfully.');
    }

    /**
     * Update an existing webhook.
     */
    public function update(UpdateWebhookRequest $request, Webhook $webhook, WebhookService $webhookService): RedirectResponse
    {
        abort_if($webhook->team_id !== active_team_id(), 403);

        $webhookService->updateWebhook($webhook, $request->validated());

        return back()->with('success', 'Webhook updated successfully.');
    }

    /**
     * Delete a webhook.
     */
    public function destroy(Webhook $webhook, WebhookService $webhookService): RedirectResponse
    {
        abort_if($webhook->team_id !== active_team_id(), 403);

        $webhookService->deleteWebhook($webhook);

        return back()->with('success', 'Webhook deleted successfully.');
    }

    /**
     * Return available webhook HTTP methods.
     *
     * @return array<string>
     */
    public static function availableMethods(): array
    {
        return ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
    }

    /**
     * Return available webhook events as label/value pairs.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function availableEvents(): array
    {
        return array_map(
            fn (WebhookEvent $event) => [
                'value' => $event->value,
                'label' => $event->label(),
            ],
            WebhookEvent::cases()
        );
    }
}
