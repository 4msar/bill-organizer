<?php

namespace App\Http\Resources;

use App\Notifications\UpcomingBillNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = DatabaseNotification::class;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'created_time' => $this->created_at->diffForHumans(),
            'url' => $this->getLink()
        ];
    }

    /**
     * Get the title of the notification.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return match ($this->type) {
            UpcomingBillNotification::class => "Upcoming Bill ({$this->data['title']})",
            default => 'Notification'
        };
    }

    /**
     * Get the description of the notification.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return match ($this->type) {
            UpcomingBillNotification::class => UpcomingBillNotification::getDescription($this),
            default => 'You have a new notification.'
        };
    }

    /**
     * Get the link of the notification.
     *
     * @return string
     */
    public function getLink(): string
    {
        return match ($this->type) {
            UpcomingBillNotification::class => route('bills.show', $this->data['bill_id']),
            default => null
        };
    }
}
