<?php


namespace App\Services\Customer;


use App\Models\Product;
use App\Models\ProductCategory;


class CategoryService
{
    /**
     * Return list of categories with product counts (only approved & not blocked products)
     */
    public function index()
    {
        $categories = ProductCategory::withCount(['products' => function ($q) {
            $q->approved()->whereHas('seller', fn($sq) => $sq->where('is_approved', true)->where('is_blocked', false));
        }])->get();


        return $categories;
    }


    /**
     * Return paginated products for given category (only approved not-blocked products and sellers)
     */
    public function products(int $categoryId, array $filters = [])
    {
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 10;


        $query = Product::with(['images', 'seller'])
            ->approved()
            ->where('category_id', $categoryId)
            ->whereHas('seller', fn($q) => $q->where('is_approved', true)->where('is_blocked', false));


        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }


        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }


        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }


        $allowedSorts = ['price', 'name', 'created_at'];
        if (!empty($filters['sort']) && in_array($filters['sort'], $allowedSorts)) {
            $direction = strtolower($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($filters['sort'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }


        return $query->paginate($perPage);
    }
}
