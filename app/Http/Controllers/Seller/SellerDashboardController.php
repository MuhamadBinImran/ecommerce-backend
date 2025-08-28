<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use Carbon\Carbon;

class SellerDashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = Auth::user();
        $seller = $user->seller ?? null;

        if (!$seller) {
            return response()->json(['message' => 'Seller profile not found'], 404);
        }

        $sellerId = $seller->id;
        $cacheKey = "seller:{$sellerId}:dashboard:stats";

        return Cache::remember($cacheKey, now()->addSeconds(30), function () use ($sellerId) {
            $now = now();
            $startOfMonth = $now->copy()->startOfMonth();

            // Fetch product counts in a single query using conditional aggregation
            $productStats = Product::where('seller_id', $sellerId)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved')
                ->selectRaw('SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending')
                ->first();

            // Fetch low stock products
            $lowStockProducts = Product::where('seller_id', $sellerId)
                ->where('stock', '<=', 5)
                ->get(['id', 'name', 'stock']);

            // Revenue & orders
            $orderQuery = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.seller_id', $sellerId)
                ->whereNotIn('orders.status', ['cancelled', 'refunded']);

            $ordersCount = (clone $orderQuery)->distinct('orders.id')->count('orders.id');
            $sellerRevenueTotal = (clone $orderQuery)->sum('order_items.total');

            $revenueThisMonth = (clone $orderQuery)
                ->where('orders.created_at', '>=', $startOfMonth)
                ->sum('order_items.total');

            // Top products (with single join instead of N+1 queries)
            $topProducts = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'order_items.product_id',
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as qty_sold'),
                    DB::raw('SUM(order_items.total) as revenue')
                )
                ->where('order_items.seller_id', $sellerId)
                ->groupBy('order_items.product_id', 'products.name')
                ->orderByDesc('qty_sold')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->name,
                        'qty_sold' => (int) $item->qty_sold,
                        'revenue' => (float) $item->revenue,
                    ];
                });

            return [
                'meta' => [
                    'generated_at' => $now->toDateTimeString(),
                ],
                'products' => [
                    'total' => (int) $productStats->total,
                    'approved' => (int) $productStats->approved,
                    'pending' => (int) $productStats->pending,
                    'low_stock_list' => $lowStockProducts,
                ],
                'orders' => [
                    'orders_count' => $ordersCount,
                    'revenue_total' => (float) $sellerRevenueTotal,
                    'revenue_this_month' => (float) $revenueThisMonth,
                ],
                'insights' => [
                    'top_products' => $topProducts,
                ],
            ];
        });
    }
}
