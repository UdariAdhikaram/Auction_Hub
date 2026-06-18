<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Jobs\ProcessEndedAuction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EndAuctions extends Command
{
    protected $signature = 'auctions:end';
    protected $description = 'Process auctions that have ended';

    public function handle(): void
    {
        // Cluster safety: Use cache to prevent multiple servers running this
        $lockKey = 'auctions:end:lock';

        if (!Cache::add($lockKey, true, 300)) {
            $this->warn('Another instance is already running.');
            return;
        }

        try {
            $endedAuctions = Auction::where('status', 'live')
                ->where('ends_at', '<=', now())
                ->get();

            $this->info("Found {$endedAuctions->count()} auctions to end.");

            foreach ($endedAuctions as $auction) {
                ProcessEndedAuction::dispatch($auction);
                $this->line("Dispatched job for auction ID: {$auction->id}");
            }

            Log::info('EndAuctions command completed', [
                'processed' => $endedAuctions->count(),
            ]);
        } finally {
            Cache::forget($lockKey);
        }

        return;
    }
}
