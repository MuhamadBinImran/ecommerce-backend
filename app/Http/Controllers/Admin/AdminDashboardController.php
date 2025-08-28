<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    // Configurable constants for thresholds
    private const LOW_STOCK_THRESHOLD = 5;
    private const CACHE_TTL_SECONDS = 60;

    /**
     * Return aggregated dashboard stats for admin.
     */
    public function stats(Request $request)
    {
        // Optionally allow query params for timeframes or force refresh
        $cacheKey = 'admin:dashboard:stats';
        $forceRefresh = $request->boolean('refresh', false);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $startOfToday = $now->copy()->startOfDay();

            // Users
            $totalUsers = User::count();
            $newUsersLast7 = User::where('created_at', '>=', $now->copy()->subDays(7))->count();
            $activeUsers30 = DB::table('sessions')
                ->where('last_activity', '>=', $now->copy()->subDays(30)->timestamp)
                ->whereNotNull('user_id')
                ->distinct()
                ->count('user_id');

            // Sellers
            $totalSellers = Seller::count();
            $approvedSellers = Seller::where('is_approved', true)->count();
            $blockedSellers = Seller::where('is_blocked', true)->count();
            $pendingSellers = Seller::where('is_approved', false)->count();

            // Products
            $totalProducts = Product::count();
            $approvedProducts = Product::where('is_approved', true)->count();
            $pendingProducts = Product::where('is_approved', false)->count();
            $blockedProducts = Product::where('is_blocked', true)->count();
            $lowStockCount = Product::where('stock', '<=', self::LOW_STOCK_THRESHOLD)->count();
            $outOfStockCount = Product::where('stock', '<=', 0)->count();

            // Orders & Revenue
            $excludedStatuses = ['cancelled', 'refunded'];
            $totalOrders = Order::count();

            $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $revenueTotal = (float) Order::whereNotIn('status', $excludedStatuses)->sum('total');
            $revenueThisMonth = (float) Order::where('created_at', '>=', $startOfMonth)
                ->whereNotIn('status', $excludedStatuses)
                ->sum('total');
            $revenueToday = (float) Order::whereDate('created_at', $startOfToday)
                ->whereNotIn('status', $excludedStatuses)
                ->sum('total');
            $avgOrderValue = $totalOrders > 0 ? round($revenueTotal / $totalOrders, 2) : 0;
            $refundsTotal = (float) Order::where('status', 'refunded')->sum('total');

            // Top Sellers by revenue (with join to avoid N+1)
            $topSellers = DB::table('order_items')
                ->join('sellers', 'order_items.seller_id', '=', 'sellers.id')
                ->select(
                    'order_items.seller_id',
                    'sellers.company_name',
                    DB::raw('SUM(order_items.total) as revenue'),
                    DB::raw('SUM(order_items.quantity) as qty_sold')
                )
                ->groupBy('order_items.seller_id', 'sellers.company_name')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get()
                ->map(function ($row) {
                    return [
                        'seller_id' => $row->seller_id,
                        'company_name' => $row->company_name ?? 'N/A',
                        'revenue' => (float) $row->revenue,
                        'qty_sold' => (int) $row->qty_sold,
                    ];
                });

            // Top Products by quantity sold (with join)
            $topProducts = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'order_items.product_id',
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as qty_sold'),
                    DB::raw('SUM(order_items.total) as revenue')
                )
                ->groupBy('order_items.product_id', 'products.name')
                ->orderByDesc('qty_sold')
                ->limit(10)
                ->get()
                ->map(function ($row) {
                    return [
                        'product_id' => $row->product_id,
                        'name' => $row->name ?? 'Unknown',
                        'qty_sold' => (int) $row->qty_sold,
                        'revenue' => (float) $row->revenue,
                    ];
                });

            // Low stock sample
            $lowStockProducts = Product::where('stock', '<=', self::LOW_STOCK_THRESHOLD)
                ->orderBy('stock', 'asc')
                ->limit(10)
                ->get(['id', 'name', 'stock']);

            return [
                'meta' => [
                    'generated_at' => $now->toDateTimeString(),
                    'cache_ttl_seconds' => self::CACHE_TTL_SECONDS
                ],
                'users' => [
                    'total' => $totalUsers,
                    'new_last_7_days' => $newUsersLast7,
                    'active_last_30_days' => $activeUsers30,
                ],
                'sellers' => [
                    'total' => $totalSellers,
                    'approved' => $approvedSellers,
                    'blocked' => $blockedSellers,
                    'pending' => $pendingSellers,
                ],
                'products' => [
                    'total' => $totalProducts,
                    'approved' => $approvedProducts,
                    'pending' => $pendingProducts,
                    'blocked' => $blockedProducts,
                    'low_stock_count' => $lowStockCount,
                    'out_of_stock_count' => $outOfStockCount,
                    'low_stock_sample' => $lowStockProducts,
                ],
                'orders' => [
                    'total' => $totalOrders,
                    'by_status' => $ordersByStatus,
                    'revenue_total' => $revenueTotal,
                    'revenue_this_month' => $revenueThisMonth,
                    'revenue_today' => $revenueToday,
                    'avg_order_value' => $avgOrderValue,
                    'refunds_total' => $refundsTotal,
                ],
                'insights' => [
                    'top_sellers' => $topSellers,
                    'top_products' => $topProducts,
                ],
            ];
        });
    }
}
