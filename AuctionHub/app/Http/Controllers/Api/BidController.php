<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Auction;
use App\Http\Requests\StoreBidRequest;
use App\Events\BidPlaced;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    public function store(StoreBidRequest $request)
    {
        // Rate limiting
        $key = 'bids:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json([
                'message' => 'Too many bids. Please wait.',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429)->header('Retry-After', RateLimiter::availableIn($key));
        }

        RateLimiter::hit($key, 60);

        // Authorization using BidPolicy
        $auction = Auction::findOrFail($request->auction_id);
        $this->authorize('place', $auction);

        try {
            DB::transaction(function () use ($request, $auction) {
                // Lock auction to prevent race conditions
                $auction = Auction::where('id', $auction->id)
                    ->lockForUpdate()
                    ->first();

                // Verify auction is still live
                if (!$auction->is_live) {
                    throw new \Exception('Auction is no longer live');
                }

                // Verify minimum bid
                $minimumBid = $auction->current_price + $auction->bid_increment;
                if ($request->amount < $minimumBid) {
                    throw new \Exception('Bid must be at least ' . $minimumBid);
                }

                // Verify bidder is not the vendor
                if ($request->user()->id === $auction->vendor_id) {
                    throw new \Exception('Vendors cannot bid on their own auctions');
                }

                // Check deposit
                $requiredDeposit = $request->amount * 0.10;
                if ($request->user()->deposit_balance < $requiredDeposit) {
                    throw new \Exception('Insufficient deposit balance');
                }

                // Create bid
                $bid = Bid::create([
                    'user_id' => $request->user()->id,
                    'auction_id' => $auction->id,
                    'amount' => $request->amount,
                    'placed_at' => now(),
                ]);

                // Deduct deposit hold
                $request->user()->deposit_balance -= $requiredDeposit;
                $request->user()->save();

                // Update auction current price
                $auction->current_price = $request->amount;
                $auction->save();

                // Dispatch event
                event(new BidPlaced($bid));
            });

            return response()->json([
                'message' => 'Bid placed successfully',
            ], 201);

        } catch (\Exception $e) {
            // Race condition or other error
            return response()->json([
                'message' => $e->getMessage(),
            ], 409); // Conflict
        }
    }
}
