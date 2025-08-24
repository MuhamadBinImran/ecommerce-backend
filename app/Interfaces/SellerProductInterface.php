<?php

namespace App\Interfaces;

interface SellerProductInterface
{
    public function create(array $data): array;
    public function list(int $sellerId, array $filters = []): array;
    public function get(int $sellerId, int $productId): array;
    public function update(int $sellerId, int $productId, array $data): array;
    public function delete(int $sellerId, int $productId): array;
    public function updateStock(int $sellerId, int $productId, array $data): array;
}
