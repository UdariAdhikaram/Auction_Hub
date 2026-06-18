<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\MoneyCast;

class Auction extends Model
{
    protected $fillable = [
        'vendor_id',
        'category_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'reserve_price',
        'current_price',
        'bid_increment',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'reserve_price' => MoneyCast::class,
        'current_price' => MoneyCast::class,
        'bid_increment' => MoneyCast::class,
        'is_live' => 'boolean',
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function watchers()
    {
        return $this->belongsToMany(User::class, 'watchlists')
                    ->withPivot('notify_at_close')
                    ->withTimestamps();
    }

    // Accessor for minimum next bid
    public function getMinimumNextBidAttribute()
    {
        return $this->current_price + $this->bid_increment;
    }

    // Scopes
    public function scopeLive($query)
    {
        return $query->where('status', 'live')
                     ->where('starts_at', '<=', now())
                     ->where('ends_at', '>=', now());
    }

    public function scopeEndingSoon($query, int $minutes = 10)
    {
        return $query->where('status', 'live')
                     ->where('ends_at', '<=', now()->addMinutes($minutes))
                     ->where('ends_at', '>=', now());
    }
}
