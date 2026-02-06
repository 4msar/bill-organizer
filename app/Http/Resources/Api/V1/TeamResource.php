<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TeamResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'icon_url' => $this->icon_url,
            'status' => $this->status,
            'currency' => $this->currency,
            'currency_symbol' => $this->currency_symbol,
            'user_id' => $this->user_id,
            'owner' => new UserResource($this->whenLoaded('user')),
            'members' => UserResource::collection($this->whenLoaded('users')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
