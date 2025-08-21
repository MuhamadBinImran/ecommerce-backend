<?php

namespace App\Services\Admin;

use App\Interfaces\AdminUserInterface;
use App\Models\CustomerProfile;
use App\Services\ErrorLogService;

class AdminUserService implements AdminUserInterface
{
    protected ErrorLogService $errorLogger;

    public function __construct(ErrorLogService $errorLogger)
    {
        $this->errorLogger = $errorLogger;
    }

    /**
     * Get all customers with their user profiles
     */
    public function getAllCustomers(): array
    {
        try {
            $customers = CustomerProfile::with('user')->get();

            if ($customers->isEmpty()) {
                return $this->response(false, 'No customers found', []);
            }

            $data = $customers->map(fn($customer) => [
                'customer_id' => $customer->id,
                'user_id'     => $customer->user->id ?? null,
                'name'        => $customer->user->name ?? null,
                'email'       => $customer->user->email ?? null,
                'is_blocked'  => $customer->user->is_blocked ?? false,
                'profile'     => [
                    'address'     => $customer->address,
                    'phone'       => $customer->phone,
                    'city'        => $customer->city,
                    'state'       => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'country'     => $customer->country,
                ],
            ])->toArray();

            return $this->response(true, 'Customers retrieved successfully', $data);

        } catch (\Throwable $e) {
            $this->errorLogger->log($e, ['method' => __METHOD__]);
            return $this->response(false, 'Failed to retrieve customers', null, $e->getMessage());
        }
    }

    /**
     * Get a single customer by customer profile ID
     */
    public function getUserById(int $id): array
    {
        try {
            $customer = CustomerProfile::with('user')->find($id);

            if (!$customer || !$customer->user) {
                return $this->response(false, 'Customer not found');
            }

            $data = [
                'customer_id' => $customer->id,
                'user_id'     => $customer->user->id,
                'name'        => $customer->user->name,
                'email'       => $customer->user->email,
                'is_blocked'  => $customer->user->is_blocked ?? false,
                'profile'     => [
                    'address'     => $customer->address,
                    'phone'       => $customer->phone,
                    'city'        => $customer->city,
                    'state'       => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'country'     => $customer->country,
                ],
            ];

            return $this->response(true, 'Customer retrieved successfully', $data);

        } catch (\Throwable $e) {
            $this->errorLogger->log($e, ['method' => __METHOD__, 'customer_id' => $id]);
            return $this->response(false, 'Failed to retrieve customer', null, $e->getMessage());
        }
    }

    /**
     * Block or unblock a customer by profile ID
     */
    public function toggleBlockCustomerByProfileId(int $profileId, bool $block): array
    {
        try {
            $customer = CustomerProfile::with('user')->find($profileId);

            if (!$customer || !$customer->user) {
                return $this->response(false, 'Customer not found');
            }

            $customer->user->is_blocked = $block;
            $customer->user->save();

            return $this->response(
                true,
                $block ? 'Customer blocked successfully' : 'Customer unblocked successfully',
                [
                    'user_id'    => $customer->user->id,
                    'is_blocked' => $customer->user->is_blocked,
                ]
            );

        } catch (\Throwable $e) {
            $this->errorLogger->log($e, ['method' => __METHOD__, 'profile_id' => $profileId]);
            return $this->response(false, 'Failed to update customer status', null, $e->getMessage());
        }
    }

    /**
     * Standardized API response
     */
    private function response(bool $success, string $message, $data = null, ?string $error = null): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'error'   => $error,
        ];
    }
}
