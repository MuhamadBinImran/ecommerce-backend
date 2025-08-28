<?php

namespace App\Services\Customer;

use App\Interfaces\WishlistInterface;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WishlistService implements WishlistInterface
{
    /**
     * Get all wishlist items for the given user.
     */
    public function getWishlist(int $userId): array
    {
        return Wishlist::with('product')
            ->where('user_id', $userId)
            ->get()
            ->toArray();
    }

    /**
     * Add a product to the user's wishlist.
     * Throws ModelNotFoundException if product doesn't exist or isn't approved.
     */
    public function addToWishlist(int $userId, int $productId): array
    {
        // Ensure product exists and is approved
        $product = Product::approved()->findOrFail($productId);

        // Check if item already exists
        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            // Already in wishlist, return existing item
            return $existing->load('product')->toArray();
        }

        // Create new wishlist item
        $item = Wishlist::create([
            'user_id' => $userId,
            'product_id' => $product->id,
        ]);

        return $item->load('product')->toArray();
    }

    /**
     * Remove a product from the wishlist.
     * Returns true if deleted, false if not found.
     */
    public function removeFromWishlist(int $userId, int $productId): bool
    {
        return (bool) Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * Optional: Clear entire wishlist for a user.
     */
    public function clearWishlist(int $userId): int
    {
        return Wishlist::where('user_id', $userId)->delete();
    }
}
