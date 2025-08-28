<?php

namespace App\Services\Customer;

use App\Interfaces\CartInterface;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService implements CartInterface
{
    public function getCart(int $userId): array
    {
        return CartItem::with('product')
            ->where('user_id', $userId)
            ->get()
            ->toArray();
    }

    public function addToCart(int $userId, int $productId, int $quantity): array
    {
        return DB::transaction(function () use ($userId, $productId, $quantity) {
            $product = Product::where('is_approved', true)
                ->where('is_blocked', false)
                ->find($productId);

            if (!$product) {
                throw new ModelNotFoundException('Product not found or not approved.');
            }

            if ($quantity > $product->stock) {
                throw new \InvalidArgumentException('Requested quantity exceeds available stock.');
            }

            $item = CartItem::firstOrNew([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);

            $newQuantity = $item->exists ? $item->quantity + $quantity : $quantity;

            if ($newQuantity > $product->stock) {
                throw new \InvalidArgumentException('Total quantity exceeds available stock.');
            }

            $item->quantity = $newQuantity;
            $item->save();

            return $item->load('product')->toArray();
        });
    }

    public function updateCartItem(int $userId, int $productId, int $quantity): array
    {
        $item = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->firstOrFail();

        $product = Product::where('is_approved', true)
            ->where('is_blocked', false)
            ->findOrFail($productId);

        if ($quantity > $product->stock) {
            throw new \InvalidArgumentException('Requested quantity exceeds available stock.');
        }

        $item->quantity = $quantity;
        $item->save();

        return $item->load('product')->toArray();
    }

    public function removeCartItem(int $userId, int $productId): bool
    {
        return (bool) CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }
}
