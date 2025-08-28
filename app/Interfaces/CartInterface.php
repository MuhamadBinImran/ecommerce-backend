<?php

namespace App\Interfaces;

interface CartInterface
{
    public function getCart(int $userId): array;
    public function addToCart(int $userId, int $productId, int $quantity): array;
    public function updateCartItem(int $userId, int $productId, int $quantity): array;
    public function removeCartItem(int $userId, int $productId): bool;
}
