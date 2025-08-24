<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\AdminOrderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    private AdminOrderInterface $service;

    public function __construct(AdminOrderInterface $service)
    {
        $this->service = $service;
    }

    private function jsonResponse(array $response, int $successCode = 200, int $errorCode = 400): JsonResponse
    {
        return response()->json($response, $response['success'] ? $successCode : $errorCode);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'seller_id', 'user_id', 'date_from', 'date_to', 'q', 'per_page']);
        return $this->jsonResponse($this->service->index($filters));
    }

    public function show(int $id): JsonResponse
    {
        return $this->jsonResponse($this->service->show($id), 200, 404);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['processing','shipped','delivered','cancelled','refunded','disputed','returned'])],
            'tracking_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payload = array_filter([
            'tracking_id' => $validated['tracking_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->jsonResponse($this->service->updateStatus($id, $validated['status'], $payload));
    }

    public function refund(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
        ]);

        $payload = array_filter([
            'amount' => $validated['amount'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return $this->jsonResponse($this->service->refund($id, $payload));
    }

    public function dispute(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        return $this->jsonResponse($this->service->markDisputed($id, ['reason' => $validated['reason']]));
    }

    public function returned(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        return $this->jsonResponse($this->service->markReturned($id, ['reason' => $validated['reason'] ?? null]));
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => ['required', Rule::in(['processing','shipped','delivered','cancelled','refunded','disputed','returned'])],
            'tracking_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payload = array_filter([
            'tracking_id' => $validated['tracking_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->jsonResponse(
            $this->service->bulkUpdateStatus($validated['order_ids'], $validated['status'], $payload)
        );
    }
}
