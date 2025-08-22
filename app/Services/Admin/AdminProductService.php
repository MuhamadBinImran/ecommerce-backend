<?php

namespace App\Services\Admin;

use App\Interfaces\AdminProductInterface;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ErrorLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AdminProductService implements AdminProductInterface
{
    public function __construct(private ErrorLogService $logger) {}

    /**
     * Apply status filter to the query builder.
     */
    private function applyStatusFilter(Builder $query, ?string $status): void
    {
        match ($status) {
            'pending'  => $query->where('is_approved', false)->where('is_blocked', false),
            'approved' => $query->where('is_approved', true)->where('is_blocked', false),
            'rejected' => $query->where('is_approved', false)->where('is_blocked', true),
            'blocked'  => $query->where('is_blocked', true),
            default    => null,
        };
    }

    private function baseQuery(): Builder
    {
        return Product::with(['seller.user', 'category', 'images']);
    }

    private function applySearchFilter(Builder $query, ?string $search): void
    {
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
    }

    public function index(array $filters = []): array
    {
        try {
            $query = $this->baseQuery();
            $this->applyStatusFilter($query, $filters['status'] ?? null);

            if (!empty($filters['seller_id'])) {
                $query->where('seller_id', (int)$filters['seller_id']);
            }
            if (!empty($filters['category_id'])) {
                $query->where('category_id', (int)$filters['category_id']);
            }
            $this->applySearchFilter($query, $filters['q'] ?? null);

            $products = $query->orderByDesc('id')->paginate($filters['per_page'] ?? 15);

            return ['success' => true, 'message' => 'Products retrieved successfully.', 'data' => $products];
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_index', 'filters' => $filters]);
            return ['success' => false, 'message' => 'Failed to retrieve products', 'error' => $e->getMessage()];
        }
    }

    public function pending(array $filters = []): array
    {
        $filters['status'] = 'pending';
        return $this->index($filters);
    }

    public function show(int $productId): array
    {
        try {
            $product = $this->baseQuery()->findOrFail($productId);
            return ['success' => true, 'message' => 'Product retrieved successfully.', 'data' => $product];
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_show', 'product_id' => $productId]);
            return ['success' => false, 'message' => 'Product not found', 'error' => $e->getMessage()];
        }
    }

    public function listBySeller(int $sellerId, array $filters = []): array
    {
        try {
            $query = $this->baseQuery()->where('seller_id', $sellerId);
            $this->applyStatusFilter($query, $filters['status'] ?? null);
            $this->applySearchFilter($query, $filters['q'] ?? null);

            $products = $query->orderByDesc('id')->paginate($filters['per_page'] ?? 15);
            return ['success' => true, 'message' => 'Seller products retrieved successfully.', 'data' => $products];
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_listBySeller', 'seller_id' => $sellerId, 'filters' => $filters]);
            return ['success' => false, 'message' => 'Failed to retrieve seller products', 'error' => $e->getMessage()];
        }
    }

    public function approve(int $productId): array
    {
        try {
            return DB::transaction(function () use ($productId) {
                $product = Product::with('seller')->findOrFail($productId);

                if (!$product->seller?->is_approved || $product->seller?->is_blocked) {
                    return ['success' => false, 'message' => 'Cannot approve: seller not approved or is blocked'];
                }

                $product->update(['is_approved' => true, 'is_blocked' => false]);
                return ['success' => true, 'message' => 'Product approved successfully.', 'data' => $product];
            });
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_approve', 'product_id' => $productId]);
            return ['success' => false, 'message' => 'Failed to approve product', 'error' => $e->getMessage()];
        }
    }

    public function reject(int $productId): array
    {
        try {
            return DB::transaction(function () use ($productId) {
                $product = Product::findOrFail($productId);
                $product->update(['is_approved' => false, 'is_blocked' => true]);
                return ['success' => true, 'message' => 'Product rejected successfully.', 'data' => $product];
            });
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_reject', 'product_id' => $productId]);
            return ['success' => false, 'message' => 'Failed to reject product', 'error' => $e->getMessage()];
        }
    }

    public function block(int $productId, bool $block): array
    {
        try {
            return DB::transaction(function () use ($productId, $block) {
                $product = Product::findOrFail($productId);
                $data = ['is_blocked' => $block];
                if ($block) {
                    $data['is_approved'] = false;
                }
                $product->update($data);
                return ['success' => true, 'message' => $block ? 'Product blocked successfully.' : 'Product unblocked successfully.', 'data' => $product];
            });
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_block', 'product_id' => $productId, 'block' => $block]);
            return ['success' => false, 'message' => 'Failed to update block status', 'error' => $e->getMessage()];
        }
    }

    public function destroy(int $productId): array
    {
        try {
            $product = Product::findOrFail($productId);
            $product->delete();
            return ['success' => true, 'message' => 'Product deleted successfully.'];
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_destroy', 'product_id' => $productId]);
            return ['success' => false, 'message' => 'Failed to delete product', 'error' => $e->getMessage()];
        }
    }

    public function bulkApprove(array $ids): array
    {
        try {
            DB::transaction(function () use ($ids) {
                Product::whereIn('id', $ids)
                    ->whereHas('seller', fn($q) => $q->where('is_approved', true)->where('is_blocked', false))
                    ->update(['is_approved' => true, 'is_blocked' => false]);
            });
            return ['success' => true, 'message' => 'Products approved successfully.'];
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_bulkApprove', 'ids' => $ids]);
            return ['success' => false, 'message' => 'Bulk approval failed', 'error' => $e->getMessage()];
        }
    }

    public function bulkReject(array $ids): array
    {
        try {
            DB::transaction(function () use ($ids) {
                Product::whereIn('id', $ids)->update(['is_approved' => false, 'is_blocked' => true]);
            });
            return ['success' => true, 'message' => 'Products rejected successfully.'];
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_bulkReject', 'ids' => $ids]);
            return ['success' => false, 'message' => 'Bulk rejection failed', 'error' => $e->getMessage()];
        }
    }

    public function bulkDelete(array $ids): array
    {
        try {
            DB::transaction(function () use ($ids) {
                Product::whereIn('id', $ids)->delete();
            });
            return ['success' => true, 'message' => 'Products deleted successfully.'];
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_bulkDelete', 'ids' => $ids]);
            return ['success' => false, 'message' => 'Bulk delete failed', 'error' => $e->getMessage()];
        }
    }

    public function removeImage(int $productId, int $imageId): array
    {
        try {
            $image = ProductImage::where('product_id', $productId)->where('id', $imageId)->firstOrFail();
            $image->delete();
            return ['success' => true, 'message' => 'Image removed successfully.'];
        } catch (\Throwable $e) {
            $this->logger->log($e, ['action' => 'admin_products_removeImage', 'product_id' => $productId, 'image_id' => $imageId]);
            return ['success' => false, 'message' => 'Failed to remove image', 'error' => $e->getMessage()];
        }
    }
}
