<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\MoneyCast;

class Bid extends Model
{
    protected $fillable = [
        'user_id',
        'auction_id',
        'amount',
        'placed_at',
    ];

    protected $casts = [
        'amount' => MoneyCast::class,
        'placed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }

    // Scope: Get winning bids (highest per auction)
    public function scopeWinning($query)
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('bids as b')
                ->whereColumn('b.auction_id', 'bids.auction_id')
                ->groupBy('b.auction_id');
        });
    }
}
