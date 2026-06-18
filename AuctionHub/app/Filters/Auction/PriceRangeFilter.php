<?php

namespace App\Filters\Auction;

use Illuminate\Database\Eloquent\Builder;

class PriceRangeFilter implements AuctionFilterInterface
{
    public function apply(Builder $query, $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        $parts = explode(',', $value);

        if (count($parts) === 2) {
            $min = $parts[0];
            $max = $parts[1];

            if ($min) {
                $query->where('current_price', '>=', $min);
            }
            if ($max) {
                $query->where('current_price', '<=', $max);
            }
        }

        return $query;
    }
}
