<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TransactionResource extends JsonResource
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
            'tnx_id' => $this->tnx_id,
            'amount' => (float) $this->amount,
            'payment_date' => $this->payment_date?->toISOString(),
            'payment_method' => $this->payment_method,
            'payment_method_name' => $this->payment_method_name,
            'attachment' => $this->attachment,
            'attachment_link' => $this->attachment_link,
            'notes' => $this->notes,
            'user_id' => $this->user_id,
            'team_id' => $this->team_id,
            'bill_id' => $this->bill_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'bill' => new BillResource($this->whenLoaded('bill')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
