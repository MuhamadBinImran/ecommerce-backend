<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAuthService;
use App\Services\Admin\AdminUserService;
use App\Services\ErrorLogService;

class AdminAuthController extends Controller
{
    protected AdminAuthService $authService;
    protected AdminUserService $userService;
    protected ErrorLogService $errorLogService;

    public function __construct(
        AdminAuthService $authService,
        AdminUserService $userService,
        ErrorLogService $errorLogService
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->errorLogService = $errorLogService;
    }

    /**
     * Multi-role login (admin, customer, seller)
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            // Allow any role (multi-role login)
            $response = $this->authService->login($data, null);

            // Blocked check for users
            if (!empty($response['data']['user']) && $response['data']['user']['is_blocked']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been blocked. Contact support.',
                    'data'    => null,
                ], 403);
            }

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message'],
                'data'    => $response['data'] ?? null,
            ], $response['success'] ? 200 : 401);

        } catch (\Throwable $e) {
            $this->errorLogService->log($e, ['action' => 'multi_role_login']);
            return response()->json([
                'success' => false,
                'message' => 'Login failed due to server error.',
                'error'   => config('app.debug') ? $e->getMessage() : 'ERR_LOGIN_' . substr(md5($e->getMessage()), 0, 8),
            ], 500);
        }
    }

    /**
     * Unified logout for all roles
     */
    public function logout(): JsonResponse
    {
        try {
            $response = $this->authService->logout();
            return response()->json([
                'success' => $response['success'],
                'message' => $response['message'],
            ], $response['success'] ? 200 : 500);

        } catch (\Throwable $e) {
            $this->errorLogService->log($e, ['action' => 'multi_role_logout']);
            return response()->json([
                'success' => false,
                'message' => 'Logout failed due to server error.',
                'error'   => config('app.debug') ? $e->getMessage() : 'ERR_LOGOUT_' . substr(md5($e->getMessage()), 0, 8),
            ], 500);
        }
    }

    /**
     * Admin-only: Block or unblock a customer
     */
    public function blockCustomer(int $customerId): JsonResponse
    {
        try {
            $block = request()->boolean('block');
            $response = $this->userService->toggleBlockCustomerByProfileId($customerId, $block);

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message'],
                'data'    => $response['data'] ?? null,
            ], $response['success'] ? 200 : 404);

        } catch (\Throwable $e) {
            $this->errorLogService->log($e, ['action' => 'block_customer', 'customer_id' => $customerId]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to block/unblock customer',
                'error'   => config('app.debug') ? $e->getMessage() : 'ERR_BLOCK_' . substr(md5($e->getMessage()), 0, 8),
            ], 500);
        }
    }
}
