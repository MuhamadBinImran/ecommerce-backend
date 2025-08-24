<?php

namespace App\Interfaces;

interface SellerOrderInterface
{
    public function list(int $sellerId, array $filters = []): array;
    public function get(int $sellerId, int $orderId): array;
    public function updateStatus(int $sellerId, int $orderId, string $status): array;
    public function requestReturn(int $sellerId, int $orderId, ?string $reason = null): array;
}
