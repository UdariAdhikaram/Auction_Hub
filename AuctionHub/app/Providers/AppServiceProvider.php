<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Bid;
use App\Observers\BidObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Bid::observe(BidObserver::class);
    }
}
