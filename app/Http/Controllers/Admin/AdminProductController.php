<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\AdminProductInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function __construct(private AdminProductInterface $productService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'seller_id', 'category_id', 'q', 'per_page']);
        return $this->jsonResponse($this->productService->index($filters));
    }

    public function pending(Request $request): JsonResponse
    {
        $filters = $request->only(['seller_id', 'category_id', 'q', 'per_page']);
        return $this->jsonResponse($this->productService->pending($filters));
    }

    public function show(int $id): JsonResponse
    {
        $response = $this->productService->show($id);
        return response()->json($response, $response['success'] ? 200 : 404);
    }

    public function listBySeller(Request $request, int $sellerId): JsonResponse
    {
        $filters = $request->only(['status', 'q', 'per_page']);
        return $this->jsonResponse($this->productService->listBySeller($sellerId, $filters));
    }

    public function approve(int $id): JsonResponse
    {
        return $this->jsonResponse($this->productService->approve($id));
    }

    public function reject(int $id): JsonResponse
    {
        return $this->jsonResponse($this->productService->reject($id));
    }

    public function block(Request $request, int $id): JsonResponse
    {
        $block = $request->boolean('block', true);
        return $this->jsonResponse($this->productService->block($id, $block));
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->jsonResponse($this->productService->destroy($id));
    }

    public function bulkApprove(Request $request): JsonResponse
    {
        return $this->jsonResponse($this->productService->bulkApprove($this->validateIds($request)));
    }

    public function bulkReject(Request $request): JsonResponse
    {
        return $this->jsonResponse($this->productService->bulkReject($this->validateIds($request)));
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->jsonResponse($this->productService->bulkDelete($this->validateIds($request)));
    }

    public function removeImage(int $productId, int $imageId): JsonResponse
    {
        return $this->jsonResponse($this->productService->removeImage($productId, $imageId));
    }

    private function jsonResponse(array $response): JsonResponse
    {
        return response()->json($response, $response['success'] ? 200 : 400);
    }

    private function validateIds(Request $request): array
    {
        return (array)$request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|distinct'
        ])['ids'];
    }
}
