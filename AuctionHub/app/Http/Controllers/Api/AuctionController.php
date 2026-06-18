<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Http\Resources\AuctionResource;
use App\Http\Resources\AuctionDetailResource;
use App\Http\Requests\StoreAuctionRequest;
use App\Filters\Auction\AuctionFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuctionController extends Controller
{
    protected $auctionFilter;

    public function __construct(AuctionFilter $auctionFilter)
    {
        $this->auctionFilter = $auctionFilter;
    }

    /**
     * GET /api/auctions - Paginated with filters
     */
    public function index(Request $request)
    {
        $query = Auction::query()
            ->with(['vendor', 'category'])
            ->where('status', '!=', 'draft');

        // Apply filters using pipeline pattern
        $query = $this->auctionFilter->apply($request, $query);

        $auctions = $query->paginate(20);

        return AuctionResource::collection($auctions);
    }

    /**
     * GET /api/auctions/{id} - With eager loading and caching
     */
    public function show(Auction $auction)
    {
        // Cache per auction for 30 seconds with tag-based invalidation
        $cacheKey = 'auction_' . $auction->id;

        $auctionData = Cache::tags(['auctions'])->remember($cacheKey, 30, function () use ($auction) {
            // Eager load relationships in ≤ 3 queries
            return $auction->load([
                'vendor',
                'vendor.vendor', // Load vendor details
                'category',
                'attachments',
                'bids' => function ($query) {
                    $query->orderBy('amount', 'desc')->limit(5);
                },
                'bids.user',
            ]);
        });

        return new AuctionDetailResource($auctionData);
    }

    /**
     * POST /api/auctions - Vendor-only
     */
    public function store(StoreAuctionRequest $request)
    {
        // Authorization handled in policy
        $this->authorize('create', Auction::class);

        $auction = Auction::create($request->validated());

        // Invalidate cache tags
        Cache::tags(['auctions'])->flush();

        return new AuctionResource($auction);
    }

    /**
     * DELETE /api/auctions/{id} - Soft delete
     */
    public function destroy(Auction $auction)
    {
        $this->authorize('delete', $auction);

        // Check if bids exist
        if ($auction->bids()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete auction with existing bids'
            ], 422);
        }

        $auction->delete();

        // Invalidate cache
        Cache::tags(['auctions'])->flush();

        return response()->json([
            'message' => 'Auction deleted successfully'
        ]);
    }

    /**
     * GET /api/auctions/ending-soon
     */
    public function endingSoon(Request $request)
    {
        $minutes = $request->input('minutes', 10);

        $auctions = Auction::endingSoon($minutes)
            ->with(['vendor', 'category'])
            ->get();

        return AuctionResource::collection($auctions);
    }
}
