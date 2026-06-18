<?php

namespace App\Observers;

use App\Models\Bid;
use App\Models\Auction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BidObserver
{
    public function creating(Bid $bid)
    {
        DB::transaction(function () use ($bid) {
            // 1. Lock the auction row to prevent race conditions
            $auction = Auction::where('id', $bid->auction_id)
                ->lockForUpdate()
                ->first();

            if (!$auction) {
                throw new \Exception('Auction not found');
            }

            // 2. Check if auction is live
            if (!$auction->is_live) {
                throw new \Exception('Auction is not live');
            }

            // 3. Check minimum bid
            $minimumNextBid = $auction->current_price + $auction->bid_increment;
            if ($bid->amount < $minimumNextBid) {
                throw new \Exception('Bid amount is below minimum next bid');
            }

            // 4. Check deposit balance (10% of bid)
            $requiredDeposit = $bid->amount * 0.10;
            if ($bid->user->deposit_balance < $requiredDeposit) {
                throw new \Exception('Insufficient deposit balance');
            }

            // 5. Prevent self-bidding
            if ($bid->user_id === $auction->vendor_id) {
                throw new \Exception('Vendor cannot bid on their own auction');
            }

            // 6. Deduct deposit hold
            $bid->user->deposit_balance -= $requiredDeposit;
            $bid->user->save();

            // 7. Update auction current price
            $auction->current_price = $bid->amount;
            $auction->save();

            // Set placed_at timestamp
            $bid->placed_at = now();
        });

        return true;
    }
}
