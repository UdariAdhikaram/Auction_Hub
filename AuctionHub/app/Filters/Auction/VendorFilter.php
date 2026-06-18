<?php

namespace App\Filters\Auction;

use Illuminate\Database\Eloquent\Builder;

class VendorFilter implements AuctionFilterInterface
{
    public function apply(Builder $query, $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        return $query->whereHas('vendor.vendor', function ($q) use ($value) {
            $q->where('store_slug', $value);
        });
    }
}
