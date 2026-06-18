<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
    ];

    // Parent relationship
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Children relationship
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Get all descendants recursively
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    // Scope for recursive descendants
    public function scopeDescendants($query, $categoryId)
    {
        return $query->where('parent_id', $categoryId)
                     ->orWhereIn('parent_id', function ($sub) use ($categoryId) {
                         $sub->select('id')
                             ->from('categories')
                             ->where('parent_id', $categoryId);
                     });
    }

    // Auctions in this category
    public function auctions()
    {
        return $this->hasMany(Auction::class);
    }
}
