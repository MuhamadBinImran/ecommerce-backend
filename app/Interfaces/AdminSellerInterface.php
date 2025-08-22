<?php

namespace App\Interfaces;

/**
 * Interface AdminSellerInterface
 *
 * Defines the contract for all admin-level seller management operations.
 */
interface AdminSellerInterface
{
    /**
     * Get all sellers.
     *
     * @return array
     */
    public function getAllSellers(): array;

    /**
     * Get a specific seller by ID.
     *
     * @param int $id
     * @return array
     */
    public function getSellerById(int $id): array;

    /**
     * Approve or unapprove a seller and trigger mail notification.
     *
     * @param int $id
     * @param bool $approve
     * @return array
     */
    public function toggleApproveSeller(int $id, bool $approve): array;

    /**
     * Block or unblock a seller and trigger mail notification.
     *
     * @param int $id
     * @param bool $block
     * @return array
     */
    public function toggleBlockSeller(int $id, bool $block): array;

    /**
     * Send a custom notification email to the seller.
     *
     * @param int $id Seller ID
     * @param string $subject Email subject
     * @param string $message Email message body
     * @return bool True if mail sent successfully, false otherwise
     */
}
