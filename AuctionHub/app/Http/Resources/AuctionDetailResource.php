<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionDetailResource extends JsonResource
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
            'minimum_next_bid' => (string) $this->minimum_next_bid,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'is_live' => $this->is_live,
            'status' => $this->status,
            'vendor' => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'email' => $this->vendor->email,
                'store_slug' => $this->vendor->vendor?->store_slug,
                'approved_at' => $this->vendor->vendor?->approved_at,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'parent_id' => $this->category->parent_id,
            ],
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'top_bids' => BidResource::collection($this->whenLoaded('bids')),
            'bid_count' => $this->bids()->count(),
        ];
    }
}
