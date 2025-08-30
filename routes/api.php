<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\Customer\CustomerRegistrationController;
use App\Http\Controllers\Customer\ProductBrowseController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\Seller\SellerAuthController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSellerController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;

// --- Public Routes ---
Route::post('login', [AdminAuthController::class, 'login']);
Route::post('sellers/register', [SellerAuthController::class, 'register']);
Route::prefix('customers/register')->group(function () {
    Route::post('request-otp', [CustomerRegistrationController::class, 'requestOtp']);
    Route::post('verify', [CustomerRegistrationController::class, 'verify']);
    Route::post('resend-otp', [CustomerRegistrationController::class, 'resend']);
});
Route::get('products', [ProductBrowseController::class, 'index']);
Route::get('products/{id}', [ProductBrowseController::class, 'show'])->whereNumber('id');

Route::get('categories', [\App\Http\Controllers\Customer\CategoryController::class, 'index']);
Route::get('categories/{id}/products', [\App\Http\Controllers\Customer\CategoryController::class, 'products'])->whereNumber('id');
// --- Protected Routes ---
Route::middleware(['auth.api'])->group(function () {

    // Logout (Unified for all roles)
    Route::post('logout', [AdminAuthController::class, 'logout']);

    /**
     * ======================
     * ADMIN ROUTES (JWT + Role:admin)
     * ======================
     */
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        /**
         * --- Customer Management ---
         */
        Route::prefix('customers')->group(function () {
            Route::get('/', [AdminUserController::class, 'index']); // List all customers
            Route::get('{id}', [AdminUserController::class, 'show'])->whereNumber('id'); // Customer details
            Route::patch('{id}/block', [AdminUserController::class, 'blockCustomer'])->whereNumber('id'); // Block/unblock customer
        });

        /**
         * --- Seller Management ---
         */
        Route::prefix('sellers')->group(function () {
            Route::get('/', [AdminSellerController::class, 'index']);
            Route::get('categories', [SellerProductController::class, 'categories']);
            Route::get('{id}', [AdminSellerController::class, 'show'])->whereNumber('id');
            Route::patch('{id}/approve', [AdminSellerController::class, 'approve'])->whereNumber('id');
            Route::patch('{id}/block', [AdminSellerController::class, 'block'])->whereNumber('id');
            Route::get('{sellerId}/products', [AdminProductController::class, 'listBySeller'])->whereNumber('sellerId');
        });

        /**
         * --- Product Management ---
         */
        Route::prefix('products')->group(function () {
            // Bulk actions
            Route::patch('bulk/approve', [AdminProductController::class, 'bulkApprove']);
            Route::patch('bulk/reject', [AdminProductController::class, 'bulkReject']);
            Route::delete('bulk/delete', [AdminProductController::class, 'bulkDelete']);

            // Listing & details
            Route::get('/', [AdminProductController::class, 'index']);
            Route::get('pending', [AdminProductController::class, 'pending']);
            Route::get('{id}', [AdminProductController::class, 'show'])->whereNumber('id');

            // Individual moderation
            Route::patch('{id}/approve', [AdminProductController::class, 'approve'])->whereNumber('id');
            Route::patch('{id}/reject', [AdminProductController::class, 'reject'])->whereNumber('id');
            Route::patch('{id}/block', [AdminProductController::class, 'block'])->whereNumber('id');
            Route::delete('{id}', [AdminProductController::class, 'destroy'])->whereNumber('id');

            // Product assets
            Route::delete('{productId}/images/{imageId}', [AdminProductController::class, 'removeImage'])
                ->whereNumber('productId')
                ->whereNumber('imageId');
        });

        /**
         * --- Order Management ---
         */
        Route::prefix('orders')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index']);
            Route::get('{id}', [AdminOrderController::class, 'show'])->whereNumber('id');
            Route::patch('{id}', [AdminOrderController::class, 'update'])->whereNumber('id');
            Route::post('{id}/refund', [AdminOrderController::class, 'refund'])->whereNumber('id');
            Route::patch('bulk/update', [AdminOrderController::class, 'bulkUpdate']);
            Route::patch('{id}/dispute', [AdminOrderController::class, 'dispute'])->whereNumber('id');
            Route::patch('{id}/returned', [AdminOrderController::class, 'returned'])->whereNumber('id');
        });

        // inside admin middleware group (prefix 'admin')
        Route::get('dashboard/stats', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'stats']);


    });

    /**
     * ======================
     * CUSTOMER ROUTES
     * ======================
     */
    Route::middleware(['role:customer'])->prefix('customer')->group(function () {
        // Define customer-specific routes here
        Route::get('profile', [CustomerProfileController::class, 'show']);
        Route::patch('profile', [CustomerProfileController::class, 'update']);
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart', [CartController::class, 'store']);
        Route::patch('cart/{productId}', [CartController::class, 'update']);
        Route::delete('cart/{productId}', [CartController::class, 'destroy']);
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);
        Route::delete('/wishlist', [WishlistController::class, 'clear']);

        Route::post('checkout', [CheckoutController::class, 'store']);    // POST /api/customer/checkout
        Route::get('orders', [CheckoutController::class, 'index']);       // GET /api/customer/orders
        Route::get('orders/{id}', [CheckoutController::class, 'show']);
    });

    /**
     * ======================
     * SELLER ROUTES
     * ======================
     */
    Route::middleware(['role:seller'])->prefix('seller')->group(function () {
        // Define seller-specific routes here
        Route::get('profile', [SellerProfileController::class, 'show']);
        Route::patch('profile', [SellerProfileController::class, 'update']);
        Route::get('products', [SellerProductController::class, 'index']);
        Route::get('categories', [SellerProductController::class, 'categories']);
        Route::post('products', [SellerProductController::class, 'store']);
        Route::get('products/{id}', [SellerProductController::class, 'show']);
        Route::patch('products/{id}', [SellerProductController::class, 'update']);
        Route::delete('products/{id}', [SellerProductController::class, 'destroy']);
        Route::patch('products/{id}/stock', [SellerProductController::class, 'updateStock']);
        // Orders
        Route::get('orders', [SellerOrderController::class, 'index']);
        Route::get('orders/{id}', [SellerOrderController::class, 'show'])->whereNumber('id');
        Route::patch('orders/{id}/status', [SellerOrderController::class, 'updateStatus'])->whereNumber('id');
        Route::patch('orders/{id}/return', [SellerOrderController::class, 'requestReturn'])->whereNumber('id');


// inside seller middleware group (prefix 'seller')
        Route::get('dashboard/stats', [\App\Http\Controllers\Seller\SellerDashboardController::class, 'stats']);
    });
});
