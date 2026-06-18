<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WatchlistController extends Controller
{
    public function index(Request $request)
    {
        // Get watchlist with latest bid using subquery
        // ONE query with subquery selects - no N+1
        $watchlist = $request->user()
            ->watchlists()
            ->select('auctions.*')
            ->selectSub(
                // Subquery to get latest bid amount
                function ($query) {
                    $query->select('amount')
                        ->from('bids')
                        ->whereColumn('bids.auction_id', 'auctions.id')
                        ->orderBy('placed_at', 'desc')
                        ->limit(1);
                },
                'latest_bid_amount'
            )
            ->selectSub(
                // Subquery to get latest bidder name
                function ($query) {
                    $query->select('users.name')
                        ->from('bids')
                        ->join('users', 'bids.user_id', '=', 'users.id')
                        ->whereColumn('bids.auction_id', 'auctions.id')
                        ->orderBy('placed_at', 'desc')
                        ->limit(1);
                },
                'latest_bidder_name'
            )
            ->with(['vendor', 'category'])
            ->get();

        return response()->json([
            'data' => $watchlist->map(function ($auction) {
                return [
                    'id' => $auction->id,
                    'title' => $auction->title,
                    'current_price' => (string) $auction->current_price,
                    'status' => $auction->status,
                    'is_live' => $auction->is_live,
                    'ends_at' => $auction->ends_at?->toISOString(),
                    'vendor' => [
                        'id' => $auction->vendor->id,
                        'name' => $auction->vendor->name,
                        'store_slug' => $auction->vendor->vendor?->store_slug,
                    ],
                    'category' => [
                        'id' => $auction->category->id,
                        'name' => $auction->category->name,
                    ],
                    'latest_bid' => $auction->latest_bid_amount ? [
                        'amount' => (string) $auction->latest_bid_amount,
                        'bidder' => $auction->latest_bidder_name,
                    ] : null,
                    'notify_at_close' => $auction->pivot->notify_at_close,
                ];
            }),
        ]);
    }


}
