<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BidResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (string) $this->amount,
            'placed_at' => $this->placed_at?->toISOString(),
            'bidder' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
        ];
    }
}
