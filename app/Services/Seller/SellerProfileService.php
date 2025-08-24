<?php

namespace App\Services\Seller;

use App\Interfaces\SellerProfileInterface;
use App\Models\Seller;
use App\Services\ErrorLogService;
use Throwable;

class SellerProfileService implements SellerProfileInterface
{
    protected ErrorLogService $logger;

    public function __construct(ErrorLogService $logger)
    {
        $this->logger = $logger;
    }

    public function getProfile(int $userId): array
    {
        try {
            $seller = Seller::where('user_id', $userId)->first();
            if (!$seller) {
                return ['success' => false, 'message' => 'Seller profile not found.'];
            }
            return ['success' => true, 'data' => $seller];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'get_seller_profile', 'user_id' => $userId]);
            return ['success' => false, 'message' => 'Failed to fetch profile.', 'error' => $e->getMessage()];
        }
    }

    public function updateProfile(int $userId, array $data): array
    {
        try {
            $seller = Seller::where('user_id', $userId)->first();
            if (!$seller) {
                return ['success' => false, 'message' => 'Seller profile not found.'];
            }
            $seller->update($data);
            return ['success' => true, 'message' => 'Profile updated successfully.', 'data' => $seller];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'update_seller_profile', 'user_id' => $userId, 'payload' => $data]);
            return ['success' => false, 'message' => 'Failed to update profile.', 'error' => $e->getMessage()];
        }
    }
}
