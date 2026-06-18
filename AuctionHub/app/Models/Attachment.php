<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'path',
        'filename',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }
}
