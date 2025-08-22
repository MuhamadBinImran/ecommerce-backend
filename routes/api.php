<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSellerController;
use App\Http\Controllers\Admin\AdminProductController;

// --- Public Routes ---
Route::post('login', [AdminAuthController::class, 'login']);

// --- Protected Routes (JWT auth + Role-based Access) ---
Route::middleware(['auth.api'])->group(function () {

    // Logout (Unified for all roles)
    Route::post('logout', [AdminAuthController::class, 'logout']);

    /**
     * ======================
     * ADMIN-ONLY ROUTES
     * ======================
     */
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // --- Customer Management ---
        Route::get('customers', [AdminUserController::class, 'index']);        // List all customers
        Route::get('customers/{id}', [AdminUserController::class, 'show']);    // Show customer details
        Route::patch('customers/{id}/block', [AdminUserController::class, 'blockCustomer']); // Block/unblock customer

        // --- Seller Management ---
        Route::get('sellers', [AdminSellerController::class, 'index']);        // List all sellers
        Route::get('sellers/{id}', [AdminSellerController::class, 'show']);    // Show seller details
        Route::patch('sellers/{id}/approve', [AdminSellerController::class, 'approve']); // Approve/unapprove seller
        Route::patch('sellers/{id}/block', [AdminSellerController::class, 'block']);     // Block/unblock seller

        // --- Product Management (Moderation) ---
        Route::get('products', [AdminProductController::class, 'index']);         // List all products
        Route::get('products/pending', [AdminProductController::class, 'pending']); // List pending products
        Route::get('products/{id}', [AdminProductController::class, 'show']);     // Show product details
        Route::patch('products/{id}/approve', [AdminProductController::class, 'approve']); // Approve product
        Route::patch('products/{id}/reject', [AdminProductController::class, 'reject']);   // Reject product
        Route::patch('products/{id}/block', [AdminProductController::class, 'block']);     // Block/unblock product
        Route::delete('products/{id}', [AdminProductController::class, 'destroy']);       // Delete product

        // --- Bulk Moderation ---
        Route::patch('products/bulk/approve', [AdminProductController::class, 'bulkApprove']); // Bulk approve
        Route::patch('products/bulk/reject',  [AdminProductController::class, 'bulkReject']);  // Bulk reject
        Route::delete('products/bulk/delete', [AdminProductController::class, 'bulkDelete']);  // Bulk delete

        // --- Product Assets ---
        Route::delete('products/{productId}/images/{imageId}', [AdminProductController::class, 'removeImage']); // Delete product image

        // --- Seller-Specific Product Listings (Admin View) ---
        Route::get('sellers/{sellerId}/products', [AdminProductController::class, 'listBySeller']); // Products by seller
    });

    /**
     * ======================
     * CUSTOMER ROUTES
     * ======================
     */
    Route::middleware(['role:customer'])->prefix('customer')->group(function () {
        // Define customer-specific routes here
    });

    /**
     * ======================
     * SELLER ROUTES
     * ======================
     */
    Route::middleware(['role:seller'])->prefix('seller')->group(function () {
        // Define seller-specific routes here
    });
});
