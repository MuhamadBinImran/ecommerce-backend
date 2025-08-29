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
        if (!$this->image_path) {
            return null;
        }

        // If already has full URL (like s3), return directly
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // Normalize path and always serve from /storage/
        return asset('storage/' . ltrim(str_replace('public/', '', $this->image_path), '/'));
    }
}
