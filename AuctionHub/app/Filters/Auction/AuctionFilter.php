<?php

namespace App\Filters\Auction;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AuctionFilter
{
    protected $filters = [
        'category' => CategoryFilter::class,
        'status' => StatusFilter::class,
        'price_range' => PriceRangeFilter::class,
        'vendor' => VendorFilter::class,
    ];

    public function apply(Request $request, Builder $query): Builder
    {
        foreach ($this->filters as $key => $filterClass) {
            if ($request->has($key)) {
                $filter = app($filterClass);
                $query = $filter->apply($query, $request->input($key));
            }
        }

        return $query;
    }
}
