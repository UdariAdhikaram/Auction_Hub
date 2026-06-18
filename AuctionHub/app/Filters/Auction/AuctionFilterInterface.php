<?php

namespace App\Filters\Auction;

use Illuminate\Database\Eloquent\Builder;

interface AuctionFilterInterface
{
    public function apply(Builder $query, $value): Builder;
}
