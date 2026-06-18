<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'current_price' => (string) $this->current_price,
            'reserve_price' => (string) $this->reserve_price,
            'bid_increment' => (string) $this->bid_increment,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'is_live' => $this->is_live,
            'status' => $this->status,
            'vendor' => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'store_slug' => $this->vendor->vendor?->store_slug,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ],
            'bid_count' => $this->bids_count ?? $this->bids()->count(),
        ];
    }
}
