<?php

namespace App\Jobs;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessEndedAuction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Auction $auction
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            // Get winning bid
            $winningBid = $this->auction->bids()
                ->orderBy('amount', 'desc')
                ->first();

            if ($winningBid) {
                // Create sale record
                $sale = $this->auction->sales()->create([
                    'bid_id' => $winningBid->id,
                    'buyer_id' => $winningBid->user_id,
                    'amount' => $winningBid->amount,
                    'sold_at' => now(),
                ]);

                // Process payment (in real life, you'd integrate with payment gateway)
                // Release deposit hold
                $winningBid->user->deposit_balance -= $winningBid->amount * 0.10;
                $winningBid->user->save();

                // Notify winner and vendor
                event(new \App\Events\AuctionWon($this->auction, $winningBid));
            }

            // Update auction status
            $this->auction->status = 'ended';
            $this->auction->save();
        });
    }
}
