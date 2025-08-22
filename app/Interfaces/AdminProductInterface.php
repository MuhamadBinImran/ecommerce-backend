<?php

namespace App\Interfaces;

/**
 * Interface AdminProductInterface
 *
 * Provides contract for all admin-level product management operations.
 */
interface AdminProductInterface
{
    public function index(array $filters = []): array;

    public function pending(array $filters = []): array;

    public function show(int $productId): array;

    public function listBySeller(int $sellerId, array $filters = []): array;

    public function approve(int $productId): array;

    public function reject(int $productId): array;

    /**
     * Block or unblock a product.
     *
     * @param int $productId
     * @param bool $block True to block, false to unblock.
     */
    public function block(int $productId, bool $block): array;

    public function destroy(int $productId): array;

    public function bulkApprove(array $ids): array;

    public function bulkReject(array $ids): array;

    public function bulkDelete(array $ids): array;

    public function removeImage(int $productId, int $imageId): array;
}
