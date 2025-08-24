<?php

namespace App\Services;

use App\Interfaces\SellerAuthInterface;
use App\Models\User;
use App\Models\Seller;
use App\Services\ErrorLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class SellerAuthService implements SellerAuthInterface
{
    protected ErrorLogService $logger;

    public function __construct(ErrorLogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Register seller (user + seller profile). Seller is NOT approved by default.
     */
    public function register(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // Create user
                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);

                // Assign role 'seller' (requires role exists)
                if (method_exists($user, 'assignRole')) {
                    try {
                        $user->assignRole('seller');
                    } catch (Throwable $roleEx) {
                        // Role assignment failed — keep going but log
                        // Not fatal if role missing; admin can assign role later
                        $this->logger->log($roleEx, ['action' => 'assign_seller_role', 'user_id' => $user->id]);
                    }
                }

                // Create seller profile
                $seller = Seller::create([
                    'user_id'      => $user->id,
                    'company_name' => $data['company_name'] ?? null,
                    'phone'        => $data['phone'] ?? null,
                    'address'      => $data['address'] ?? null,
                    'city'         => $data['city'] ?? null,
                    'state'        => $data['state'] ?? null,
                    'postal_code'  => $data['postal_code'] ?? null,
                    'country'      => $data['country'] ?? null,
                    'is_approved'  => false,
                    'is_blocked'   => false,
                ]);

                // Response: do not issue token; admin must approve.
                return [
                    'success' => true,
                    'message' => 'Registration successful. Your seller account is pending admin approval.',
                    'data' => [
                        'user'   => $user->only(['id','name','email','phone']),
                        'seller' => $seller->only(['id','company_name','is_approved','is_blocked']),
                    ]
                ];
            });
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'seller_register', 'payload' => $data]);
            return [
                'success' => false,
                'message' => 'Registration failed.',
                'error'   => $e->getMessage(),
            ];
        }
    }
}
