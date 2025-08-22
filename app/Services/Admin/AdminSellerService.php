<?php

namespace App\Services\Admin;

use App\Interfaces\AdminSellerInterface;
use App\Models\Seller;
use App\Services\ErrorLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SellerApprovalMail;
use Throwable;

class AdminSellerService implements AdminSellerInterface
{
    protected ErrorLogService $errorLogger;

    public function __construct(ErrorLogService $errorLogger)
    {
        $this->errorLogger = $errorLogger;
    }

    /**
     * Get all sellers with user info
     */
    public function getAllSellers(): array
    {
        try {
            $sellers = Seller::with('user')->get();

            if ($sellers->isEmpty()) {
                return $this->response(false, 'No sellers found');
            }

            $data = $sellers->map(fn(Seller $seller) => $this->formatSeller($seller))->toArray();

            return $this->response(true, 'Sellers retrieved successfully', $data);

        } catch (Throwable $e) {
            $this->errorLogger->log($e, ['method' => __METHOD__]);
            return $this->response(false, 'Failed to retrieve sellers', null, $e->getMessage());
        }
    }

    /**
     * Get seller by ID
     */
    public function getSellerById(int $id): array
    {
        try {
            $seller = Seller::with('user')->find($id);

            if (!$seller || !$seller->user) {
                return $this->response(false, 'Seller not found');
            }

            return $this->response(true, 'Seller retrieved successfully', $this->formatSeller($seller));

        } catch (Throwable $e) {
            $this->errorLogger->log($e, ['method' => __METHOD__, 'seller_id' => $id]);
            return $this->response(false, 'Failed to retrieve seller', null, $e->getMessage());
        }
    }

    /**
     * Approve or unapprove seller
     */
    public function toggleApproveSeller(int $id, bool $approve): array
    {
        try {
            return DB::transaction(function () use ($id, $approve) {
                $seller = Seller::with('user')->find($id);

                if (!$seller || !$seller->user) {
                    return $this->response(false, 'Seller not found');
                }

                $seller->update(['is_approved' => $approve]);

                // Send approval mail if approved
                if ($approve) {
                    try {
                        Mail::to($seller->user->email)->queue(new SellerApprovalMail($seller->user));
                    } catch (Throwable $mailError) {
                        $this->errorLogger->log($mailError, ['method' => __METHOD__, 'seller_id' => $id, 'mail' => true]);
                        // Continue without failing main transaction
                    }
                }

                return $this->response(
                    true,
                    $approve ? 'Seller approved successfully' : 'Seller unapproved successfully',
                    $this->formatSeller($seller)
                );
            });

        } catch (Throwable $e) {
            $this->errorLogger->log($e, ['method' => __METHOD__, 'seller_id' => $id]);
            return $this->response(false, 'Failed to update seller approval', null, $e->getMessage());
        }
    }

    /**
     * Block or unblock seller
     */
    public function toggleBlockSeller(int $id, bool $block): array
    {
        try {
            return DB::transaction(function () use ($id, $block) {
                $seller = Seller::with('user')->find($id);

                if (!$seller || !$seller->user) {
                    return $this->response(false, 'Seller not found');
                }

                $seller->update(['is_blocked' => $block]);

                return $this->response(
                    true,
                    $block ? 'Seller blocked successfully' : 'Seller unblocked successfully',
                    $this->formatSeller($seller)
                );
            });

        } catch (Throwable $e) {
            $this->errorLogger->log($e, ['method' => __METHOD__, 'seller_id' => $id]);
            return $this->response(false, 'Failed to update seller block status', null, $e->getMessage());
        }
    }

    /**
     * Helper: format seller response
     */
    private function formatSeller(Seller $seller): array
    {
        return [
            'seller_id'   => $seller->id,
            'user_id'     => $seller->user->id ?? null,
            'name'        => $seller->user->name ?? null,
            'email'       => $seller->user->email ?? null,
            'is_approved' => (bool) $seller->is_approved,
            'is_blocked'  => (bool) $seller->is_blocked,
            'profile'     => [
                'company_name' => $seller->company_name,
                'phone'        => $seller->phone,
                'address'      => $seller->address,
                'city'         => $seller->city,
                'state'        => $seller->state,
                'postal_code'  => $seller->postal_code,
                'country'      => $seller->country,
            ],
        ];
    }

    /**
     * Standard response
     */
    private function response(bool $success, string $message, ?array $data = null, ?string $error = null): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'error'   => $error,
        ];
    }
}
