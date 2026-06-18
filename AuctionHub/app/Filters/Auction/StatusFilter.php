<?php

namespace App\Filters\Auction;

use Illuminate\Database\Eloquent\Builder;

class StatusFilter implements AuctionFilterInterface
{
    public function apply(Builder $query, $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        return $query->where('status', $value);
    }
}
