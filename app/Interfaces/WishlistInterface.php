<?php

namespace App\Interfaces;

interface WishlistInterface
{
    public function getWishlist(int $userId): array;
    public function addToWishlist(int $userId, int $productId): array;
    public function removeFromWishlist(int $userId, int $productId): bool;
}
