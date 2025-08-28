<?php

namespace App\Services\Customer;

use App\Models\Product;

class ProductBrowseService
{
    public function list(array $filters)
    {
        $query = Product::query()
            ->with(['images', 'seller'])
            ->where('is_approved', true)
            ->where('is_blocked', false)
            ->whereHas('seller', fn($q) => $q->where('is_approved', true)->where('is_blocked', false));

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['sort']) && in_array($filters['sort'], ['price', 'name', 'created_at'])) {
            $direction = $filters['direction'] ?? 'asc';
            $query->orderBy($filters['sort'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function detail(int $id)
    {
        return Product::with(['images', 'seller'])
            ->where('id', $id)
            ->where('is_approved', true)
            ->where('is_blocked', false)
            ->whereHas('seller', fn($q) => $q->where('is_approved', true)->where('is_blocked', false))
            ->firstOrFail();
    }
}
