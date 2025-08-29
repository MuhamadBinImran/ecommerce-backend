<?php

namespace App\Services\Seller;

use App\Interfaces\SellerProductInterface;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ErrorLogService;
use Illuminate\Support\Facades\DB;
use Throwable;

class SellerProductService implements SellerProductInterface
{
    protected ErrorLogService $logger;

    public function __construct(ErrorLogService $logger)
    {
        $this->logger = $logger;
    }

    public function create(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                $product = Product::create([
                    'seller_id' => $data['seller_id'],
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'price' => $data['price'],
                    'stock' => $data['stock'] ?? 0,
                    'is_approved' => false, // pending approval
                ]);

                // Save images if present
                if (!empty($data['images'])) {
                    foreach ($data['images'] as $file) {
                        $path = $file->store('products', 'public'); // saves in storage/app/public/products

                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => 'storage/' . $path
                        ]);
                    }
                }


                return ['success' => true, 'data' => $product];
            });
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'create_product', 'payload' => $data]);
            return ['success' => false, 'message' => 'Failed to create product.', 'error' => $e->getMessage()];
        }
    }

    public function list(int $sellerId, array $filters = []): array
    {
        try {
            $query = Product::where('seller_id', $sellerId);

            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (isset($filters['is_approved'])) {
                $query->where('is_approved', $filters['is_approved']);
            }

            if (!empty($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            $products = $query->with('images')->get();

            return ['success' => true, 'data' => $products];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'list_products', 'seller_id' => $sellerId]);
            return ['success' => false, 'message' => 'Failed to fetch products.', 'error' => $e->getMessage()];
        }
    }

    public function get(int $sellerId, int $productId): array
    {
        try {
            $product = Product::with('images')
                ->where('id', $productId)
                ->where('seller_id', $sellerId)
                ->first();

            if (!$product) {
                return ['success' => false, 'message' => 'Product not found.'];
            }

            return ['success' => true, 'data' => $product];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'get_product', 'product_id' => $productId]);
            return ['success' => false, 'message' => 'Failed to fetch product.', 'error' => $e->getMessage()];
        }
    }

    public function update(int $sellerId, int $productId, array $data): array
    {
        try {
            $product = Product::where('id', $productId)
                ->where('seller_id', $sellerId)
                ->first();

            if (!$product) {
                return ['success' => false, 'message' => 'Product not found.'];
            }

            // Update product fields except images
            $updateData = $data;
            unset($updateData['images']);
            $product->update($updateData);

            // Handle new images if provided
            if (!empty($data['images'])) {
                foreach ($data['images'] as $file) {
                    $path = $file->store('products', 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => 'storage/' . $path,
                    ]);
                }
            }

            // Load fresh images
            $product->load('images');

            return [
                'success' => true,
                'message' => 'Product updated.',
                'data' => $product
            ];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'update_product', 'product_id' => $productId]);
            return ['success' => false, 'message' => 'Failed to update product.', 'error' => $e->getMessage()];
        }
    }


    public function delete(int $sellerId, int $productId): array
    {
        try {
            $product = Product::where('id', $productId)
                ->where('seller_id', $sellerId)
                ->first();

            if (!$product) {
                return ['success' => false, 'message' => 'Product not found.'];
            }

            $product->delete();
            return ['success' => true, 'message' => 'Product deleted.'];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'delete_product', 'product_id' => $productId]);
            return ['success' => false, 'message' => 'Failed to delete product.', 'error' => $e->getMessage()];
        }
    }

    public function updateStock(int $sellerId, int $productId, array $data): array
    {
        try {
            $product = Product::where('id', $productId)
                ->where('seller_id', $sellerId)
                ->first();

            if (!$product) {
                return ['success' => false, 'message' => 'Product not found.'];
            }

            $updateData = [];
            if (isset($data['stock'])) $updateData['stock'] = $data['stock'];
            if (isset($data['price'])) $updateData['price'] = $data['price'];

            $product->update($updateData);
            return ['success' => true, 'message' => 'Stock/Price updated.', 'data' => $product];
        } catch (Throwable $e) {
            $this->logger->log($e, ['action' => 'update_stock', 'product_id' => $productId]);
            return ['success' => false, 'message' => 'Failed to update stock/price.', 'error' => $e->getMessage()];
        }
    }
}
