<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Services\Admin\AdminUserService;
use App\Services\ErrorLogService;

class AdminUserController extends Controller
{
    private AdminUserService $userService;
    private ErrorLogService $errorLogService;

    public function __construct(AdminUserService $userService, ErrorLogService $errorLogService)
    {
        $this->userService = $userService;
        $this->errorLogService = $errorLogService;
    }

    /**
     * Get all customers with their profile data
     */
    public function index(): JsonResponse
    {
        try {
            $response = $this->userService->getAllCustomers();

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message'],
                'data'    => $response['data'] ?? [],
            ], $response['success'] ? 200 : 404);
        } catch (\Throwable $e) {
            $this->errorLogService->log($e, ['action' => 'get_all_customers']);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customers',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server Error',
            ], 500);
        }
    }

    /**
     * Get a single customer by customer_profile.id
     */
    public function show(int $id): JsonResponse
    {
        try {
            $response = $this->userService->getUserById($id);

            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'] ?? 'Customer not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Customer retrieved successfully',
                'data'    => $response['data'],
            ], 200);
        } catch (\Throwable $e) {
            $this->errorLogService->log($e, ['action' => 'get_customer', 'customer_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server Error',
            ], 500);
        }
    }

    /**
     * Block or unblock a customer by profile ID (Admin only)
     * Query parameter: ?block=true or ?block=false
     */
    public function blockCustomer(int $id): JsonResponse
    {
        try {
            $block = request()->boolean('block');

            $response = $this->userService->toggleBlockCustomerByProfileId($id, $block);

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message'],
                'data'    => $response['data'] ?? null,
            ], $response['success'] ? 200 : 404);

        } catch (\Throwable $e) {
            $this->errorLogService->log($e, ['action' => 'block_customer', 'customer_id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to block/unblock customer',
                'error'   => config('app.debug') ? $e->getMessage() : 'Server Error',
            ], 500);
        }
    }
}
