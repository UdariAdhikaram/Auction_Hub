<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'store_slug',
        'commission_rate',
        'approved_at',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auctions()
    {
        return $this->hasMany(Auction::class, 'vendor_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
