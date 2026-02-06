<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'user_id' => $this->user_id,
            'team_id' => $this->team_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'bills' => BillResource::collection($this->whenLoaded('bills')),
            'bills_count' => $this->when(isset($this->bills_count), $this->bills_count),
            'total_bills_count' => $this->when(isset($this->total_bills_count), $this->total_bills_count),
            'unpaid_bills_count' => $this->when(isset($this->unpaid_bills_count), $this->unpaid_bills_count),
            'total_amount' => $this->when(isset($this->total_amount), $this->total_amount),
            'unpaid_amount' => $this->when(isset($this->unpaid_amount), $this->unpaid_amount),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
