<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'due_date' => $this->due_date?->toISOString(),
            'trial_start_date' => $this->trial_start_date?->toISOString(),
            'trial_end_date' => $this->trial_end_date?->toISOString(),
            'has_trial' => $this->has_trial,
            'status' => $this->status,
            'is_recurring' => $this->is_recurring,
            'recurrence_period' => $this->recurrence_period?->value,
            'payment_url' => $this->payment_url,
            'tags' => $this->tags,
            'user_id' => $this->user_id,
            'team_id' => $this->team_id,
            'category_id' => $this->category_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
