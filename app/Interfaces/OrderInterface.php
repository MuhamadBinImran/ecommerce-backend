<?php

namespace App\Interfaces;

interface OrderInterface
{
    /**
     * Create an order for a user using their cart items.
     * @param int $userId
     * @param array $payload  // shipping_address, payment meta, idempotency_key(optional)
     * @return array          // order data (with items)
     */
    public function createOrder(int $userId, array $payload): array;

    /**
     * List orders for a user (paginated)
     */
    public function listOrders(int $userId, array $filters = []): array;

    /**
     * Get single order (ensures user owns order)
     */
    public function getOrder(int $userId, int $orderId): array;
}
