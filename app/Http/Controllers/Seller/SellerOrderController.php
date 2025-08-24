<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Interfaces\SellerOrderInterface;
use App\Http\Requests\OrderStatusUpdateRequest;
use App\Http\Requests\OrderReturnRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SellerOrderController extends Controller
{
    protected SellerOrderInterface $service;

    public function __construct(SellerOrderInterface $service)
    {
        $this->service = $service;
    }

    protected function sellerId(): ?int
    {
        // IMPORTANT: seller_id comes from sellers table, not users table
        return optional(Auth::user()?->seller)->id;
    }

    public function index(): JsonResponse
    {
        $sellerId = $this->sellerId();
        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller profile not found'], 404);
        }

        $filters = request()->only(['status', 'search', 'per_page']);
        $res = $this->service->list($sellerId, $filters);
        return response()->json($res);
    }

    public function show(int $id): JsonResponse
    {
        $sellerId = $this->sellerId();
        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller profile not found'], 404);
        }

        $res = $this->service->get($sellerId, $id);
        return response()->json($res);
    }

    public function updateStatus(OrderStatusUpdateRequest $request, int $id): JsonResponse
    {
        $sellerId = $this->sellerId();
        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller profile not found'], 404);
        }

        $res = $this->service->updateStatus($sellerId, $id, $request->validated()['status']);
        return response()->json($res);
    }

    public function requestReturn(OrderReturnRequest $request, int $id): JsonResponse
    {
        $sellerId = $this->sellerId();
        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller profile not found'], 404);
        }

        $res = $this->service->requestReturn($sellerId, $id, $request->validated()['reason'] ?? null);
        return response()->json($res);
    }
}
