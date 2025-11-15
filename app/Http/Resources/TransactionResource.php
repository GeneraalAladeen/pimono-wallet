<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->amount,
            'commission_fee' => $this->commission_fee,
            'total_amount_debited' => $this->total_amount_debited,
            'created_at' => $this->created_at,
            'sender' => UserResource::make($this->whenLoaded('sender')),
            'receiver' => UserResource::make($this->whenLoaded('receiver')),
        ];
    }
}
