<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Interfaces\SellerProductInterface;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Requests\ProductStockUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SellerProductController extends Controller
{
    protected SellerProductInterface $service;

    public function __construct(SellerProductInterface $service)
    {
        $this->service = $service;
    }

    // Create product
    public function store(ProductCreateRequest $request): JsonResponse
    {
        $sellerId = Auth::user()->seller?->id;

        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller not found.'], 404);
        }

        $data = $request->validated();
        $data['seller_id'] = $sellerId;

        $response = $this->service->create($data);
        return response()->json($response, $response['success'] ? 201 : 400);
    }

    // List seller products
    public function index(): JsonResponse
    {
        $sellerId = Auth::user()->seller?->id;

        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller not found.'], 404);
        }

        $filters = request()->only(['category_id','is_approved','search']);
        $response = $this->service->list($sellerId, $filters);
        return response()->json($response);
    }

    // Get single product
    public function show(int $id): JsonResponse
    {
        $sellerId = Auth::user()->seller?->id;

        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller not found.'], 404);
        }

        $response = $this->service->get($sellerId, $id);
        return response()->json($response);
    }

    // Update product
    public function update(ProductUpdateRequest $request, int $id): JsonResponse
    {
        $sellerId = Auth::user()->seller?->id;

        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller not found.'], 404);
        }

        $data = $request->validated();
        $response = $this->service->update($sellerId, $id, $data);
        return response()->json($response);
    }

    // Delete product
    public function destroy(int $id): JsonResponse
    {
        $sellerId = Auth::user()->seller?->id;

        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller not found.'], 404);
        }

        $response = $this->service->delete($sellerId, $id);
        return response()->json($response);
    }

    // Update stock & price
    public function updateStock(ProductStockUpdateRequest $request, int $id): JsonResponse
    {
        $sellerId = Auth::user()->seller?->id;

        if (!$sellerId) {
            return response()->json(['success' => false, 'message' => 'Seller not found.'], 404);
        }

        $data = $request->validated();
        $response = $this->service->updateStock($sellerId, $id, $data);
        return response()->json($response);
    }
}
