<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'kyc_verified_at',
        'deposit_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'deposit_balance' => 'decimal:2',
    ];

    // Relationships
    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function auctions()
    {
        return $this->hasMany(Auction::class, 'vendor_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function watchlists()
    {
        return $this->belongsToMany(Auction::class, 'watchlists')
                    ->withPivot('notify_at_close')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeWithActiveBidCount($query)
    {
        return $query->withCount(['bids' => function ($query) {
            $query->whereHas('auction', function ($q) {
                $q->where('status', 'live');
            });
        }]);
    }
}
