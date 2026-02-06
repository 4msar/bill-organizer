<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class NoteResource extends JsonResource
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
            'content' => $this->content,
            'is_pinned' => $this->is_pinned,
            'user_id' => $this->user_id,
            'team_id' => $this->team_id,
            'was_recently_created' => $this->was_recently_created,
            'user' => new UserResource($this->whenLoaded('user')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'bills' => BillResource::collection($this->whenLoaded('bills')),
            'transactions' => TransactionResource::collection($this->whenLoaded('transactions')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
