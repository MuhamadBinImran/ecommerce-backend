<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\AdminSellerInterface;
use App\Services\ErrorLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Mail\SellerApprovalMail;
use Illuminate\Support\Facades\Mail;

class AdminSellerController extends Controller
{
    protected AdminSellerInterface $sellerService;
    protected ErrorLogService $errorLogService;

    public function __construct(AdminSellerInterface $sellerService, ErrorLogService $errorLogService)
    {
        $this->sellerService = $sellerService;
        $this->errorLogService = $errorLogService;
    }

    /**
     * Get all sellers with optional filters/search
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'blocked', 'search']);
            $response = $this->sellerService->getAllSellers($filters);
            return $this->sendResponse($response);
        } catch (\Throwable $e) {
            return $this->handleException(
                $e,
                'admin_get_all_sellers',
                'Failed to retrieve sellers',
                'ERR_SELLERS_001'
            );
        }
    }

    /**
     * Get a single seller by ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $response = $this->sellerService->getSellerById($id);
            return $this->sendResponse($response);
        } catch (\Throwable $e) {
            return $this->handleException(
                $e,
                'admin_get_seller',
                'Failed to retrieve seller',
                'ERR_SELLER_002',
                ['seller_id' => $id]
            );
        }
    }

    /**
     * Approve or unapprove a seller
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $approve = $request->boolean('approve', true);
            $response = $this->sellerService->toggleApproveSeller($id, $approve);

            // Send approval email if approved and email exists
            if ($response['success'] && !empty($response['data']['email'])) {
                try {
                    Mail::to($response['data']['email'])->queue(
                        new SellerApprovalMail((object) $response['data'], $approve)
                    );
                } catch (\Throwable $mailError) {
                    // Log mail failure without blocking main flow
                    $this->errorLogService->log($mailError, [
                        'action' => 'send_seller_approval_mail',
                        'seller_id' => $id
                    ]);
                }
            }

            return $this->sendResponse($response);
        } catch (\Throwable $e) {
            return $this->handleException(
                $e,
                'admin_approve_seller',
                'Failed to update seller approval status',
                'ERR_APPROVE_003',
                ['seller_id' => $id]
            );
        }
    }

    /**
     * Block or unblock a seller
     */
    public function block(Request $request, int $id): JsonResponse
    {
        try {
            $block = $request->boolean('block', true);
            $response = $this->sellerService->toggleBlockSeller($id, $block);
            return $this->sendResponse($response);
        } catch (\Throwable $e) {
            return $this->handleException(
                $e,
                'admin_block_seller',
                'Failed to update seller block status',
                'ERR_BLOCK_004',
                ['seller_id' => $id]
            );
        }
    }

    /**
     * (Optional) Bulk approve/block sellers
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'action' => 'required|string|in:approve,unapprove,block,unblock'
        ]);

        try {
            $response = $this->sellerService->bulkAction($request->input('ids'), $request->input('action'));
            return $this->sendResponse($response);
        } catch (\Throwable $e) {
            return $this->handleException(
                $e,
                'admin_bulk_seller_action',
                'Failed to perform bulk action on sellers',
                'ERR_BULK_005',
                ['ids' => $request->input('ids')]
            );
        }
    }

    /**
     * Standardized JSON response wrapper
     */
    private function sendResponse(array $response): JsonResponse
    {
        $status = $response['success'] ? 200 : ($response['code'] ?? 404);
        return response()->json([
            'success' => $response['success'],
            'message' => $response['message'] ?? '',
            'data'    => $response['data'] ?? null,
            'errors'  => $response['errors'] ?? null
        ], $status);
    }

    /**
     * Centralized exception handling
     */
    private function handleException(\Throwable $e, string $action, string $message, string $defaultCode, array $context = []): JsonResponse
    {
        $this->errorLogService->log($e, array_merge(['action' => $action], $context));

        return response()->json([
            'success' => false,
            'message' => $message,
            'error'   => config('app.debug') ? $e->getMessage() : $defaultCode,
        ], 500);
    }
}
