<?php

namespace App\Filters\Auction;

use Illuminate\Database\Eloquent\Builder;

class CategoryFilter implements AuctionFilterInterface
{
    public function apply(Builder $query, $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        // Get category and its descendants
        return $query->whereHas('category', function ($q) use ($value) {
            $q->where('id', $value)
              ->orWhereIn('id', function ($sub) use ($value) {
                  $sub->select('id')
                      ->from('categories')
                      ->where('parent_id', $value);
              });
        });
    }
}
