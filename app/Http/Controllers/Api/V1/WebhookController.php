<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\WebhookEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Webhook\StoreWebhookRequest;
use App\Http\Requests\Webhook\UpdateWebhookRequest;
use App\Http\Resources\Api\V1\WebhookDeliveryResource;
use App\Http\Resources\Api\V1\WebhookResource;
use App\Jobs\DispatchWebhookJob;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WebhookController extends Controller
{
    /**
     * List all webhooks for the active team.
     */
    public function index(Request $request): JsonResponse
    {
        $webhooks = Webhook::query()
            ->where('team_id', active_team_id())
            ->latest()
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => WebhookResource::collection($webhooks),
            'meta' => [
                'current_page' => $webhooks->currentPage(),
                'last_page' => $webhooks->lastPage(),
                'per_page' => $webhooks->perPage(),
                'total' => $webhooks->total(),
            ],
        ]);
    }

    /**
     * Create a new webhook.
     */
    public function store(StoreWebhookRequest $request, WebhookService $webhookService): JsonResponse
    {
        $webhook = $webhookService->createWebhook($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Webhook created successfully',
            'data' => new WebhookResource($webhook),
        ], 201);
    }

    /**
     * Show a specific webhook.
     */
    public function show(Webhook $webhook): JsonResponse
    {
        $this->authorizeWebhook($webhook);

        return response()->json([
            'success' => true,
            'data' => new WebhookResource($webhook),
        ]);
    }

    /**
     * Update a webhook.
     */
    public function update(UpdateWebhookRequest $request, Webhook $webhook, WebhookService $webhookService): JsonResponse
    {
        $this->authorizeWebhook($webhook);

        $webhookService->updateWebhook($webhook, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Webhook updated successfully',
            'data' => new WebhookResource($webhook->fresh()),
        ]);
    }

    /**
     * Delete a webhook.
     */
    public function destroy(Webhook $webhook, WebhookService $webhookService): JsonResponse
    {
        $this->authorizeWebhook($webhook);

        $webhookService->deleteWebhook($webhook);

        return response()->json([
            'success' => true,
            'message' => 'Webhook deleted successfully',
        ]);
    }

    /**
     * List available webhook events.
     */
    public function events(): JsonResponse
    {
        $events = array_map(
            fn (WebhookEvent $event) => [
                'value' => $event->value,
                'label' => $event->label(),
            ],
            WebhookEvent::cases()
        );

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * List deliveries for a specific webhook.
     */
    public function deliveries(Request $request, Webhook $webhook): JsonResponse
    {
        $this->authorizeWebhook($webhook);

        $deliveries = $webhook->deliveries()
            ->latest()
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => WebhookDeliveryResource::collection($deliveries),
            'meta' => [
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
                'per_page' => $deliveries->perPage(),
                'total' => $deliveries->total(),
            ],
        ]);
    }

    /**
     * Retry a failed webhook delivery.
     */
    public function retry(Webhook $webhook, WebhookDelivery $delivery): JsonResponse
    {
        $this->authorizeWebhook($webhook);

        $event = WebhookEvent::from($delivery->event);

        DispatchWebhookJob::dispatch($webhook, $event, $delivery->payload);

        return response()->json([
            'success' => true,
            'message' => 'Webhook delivery queued for retry',
        ]);
    }

    /**
     * Ensure the authenticated user belongs to the webhook's team.
     */
    private function authorizeWebhook(Webhook $webhook): void
    {
        abort_if($webhook->team_id !== active_team_id(), 403, 'Forbidden');
    }
}
