<?php

namespace App\Interfaces;

interface AdminSellerInterface
{
    /**
     * Get all sellers
     */
    public function getAllSellers(): array;

    /**
     * Get a seller by ID
     */
    public function getSellerById(int $id): array;

    /**
     * Approve or unapprove a seller and trigger mail notification
     */
    public function toggleApproveSeller(int $id, bool $approve): array;

    /**
     * Block or unblock a seller and trigger mail notification
     */
    public function toggleBlockSeller(int $id, bool $block): array;

    /**
     * Send seller approval notification
     */
}
