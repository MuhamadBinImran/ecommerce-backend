<?php

namespace App\Interfaces;

/**
 * Interface AdminOrderInterface
 *
 * Provides a contract for all admin-level order management operations.
 */
interface AdminOrderInterface
{
    /**
     * Get all orders with optional filters (status, date range, etc.).
     *
     * @param array $filters
     * @return array Associative array containing paginated orders or filtered list.
     */
    public function index(array $filters = []): array;

    /**
     * Get a specific order by its ID.
     *
     * @param int $orderId
     * @return array Details of the specific order.
     */
    public function show(int $orderId): array;

    /**
     * Update the status of a specific order.
     *
     * @param int $orderId
     * @param string $status New status (e.g., pending, completed, cancelled).
     * @param array $payload Optional additional data (e.g., reason for cancellation).
     * @return array Result containing updated order details or error info.
     */
    public function updateStatus(int $orderId, string $status, array $payload = []): array;

    /**
     * Initiate a refund for a specific order.
     *
     * @param int $orderId
     * @param array $payload Optional additional data (e.g., refund reason, amount).
     * @return array Refund response details.
     */
    public function refund(int $orderId, array $payload = []): array;

    /**
     * Mark a specific order as disputed.
     *
     * @param int $orderId
     * @param array $payload Optional additional details (e.g., dispute reason).
     * @return array Dispute response details.
     */
    public function markDisputed(int $orderId, array $payload = []): array;

    /**
     * Mark a specific order as returned.
     *
     * @param int $orderId
     * @param array $payload Optional additional details (e.g., return reason).
     * @return array Return response details.
     */
    public function markReturned(int $orderId, array $payload = []): array;

    /**
     * Bulk update the status for multiple orders.
     *
     * @param array $orderIds Array of order IDs to update.
     * @param string $status New status for all specified orders.
     * @param array $payload Optional additional data (e.g., bulk reason).
     * @return array Result containing updated order details or error info.
     */
    public function bulkUpdateStatus(array $orderIds, string $status, array $payload = []): array;
}
