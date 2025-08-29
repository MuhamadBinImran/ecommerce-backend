<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'image_path'];

    // Always include "url" in JSON responses
    protected $appends = ['url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor for full image URL
     */
    public function getUrlAttribute()
    {
        // Ensure image_path is not empty
        return $this->image_path
            ? asset($this->image_path)
            : null;
    }
}
