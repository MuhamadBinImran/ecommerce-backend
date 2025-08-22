<?php

use App\Http\Controllers\Admin\AdminProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminSellerController;

// Public login route for all roles
Route::post('login', [AdminAuthController::class, 'login']);

// Protected routes (JWT auth + role-based access)
Route::middleware(['auth.api'])->group(function () {

    // Unified logout for all authenticated users
    Route::post('logout', [AdminAuthController::class, 'logout']);

    /**
     * Admin-specific routes (only accessible to admin)
     */
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // --- Customer management ---
        Route::get('customers', [AdminUserController::class, 'index']);
        Route::get('customers/{id}', [AdminUserController::class, 'show']);
        Route::patch('customers/{id}/block', [AdminUserController::class, 'blockCustomer']);

        // --- Seller management ---
        Route::get('sellers', [AdminSellerController::class, 'index']);
        Route::get('sellers/{id}', [AdminSellerController::class, 'show']);
        Route::post('sellers/{id}/approve', [AdminSellerController::class, 'approve']);
        Route::post('sellers/{id}/block', [AdminSellerController::class, 'block']);

        // Product moderation
        Route::get('products/pending', [AdminProductController::class, 'pending']);
        Route::patch('products/{id}/approve', [AdminProductController::class, 'approve']);
        Route::patch('products/{id}/reject', [AdminProductController::class, 'reject']);
        Route::delete('products/{id}', [AdminProductController::class, 'destroy']);
    });

    /**
     * Customer-specific routes (role: customer)
     */
    Route::middleware(['role:customer'])->prefix('customer')->group(function () {
        // Add customer-specific endpoints here
    });

    /**
     * Seller-specific routes (role: seller)
     */
    Route::middleware(['role:seller'])->prefix('seller')->group(function () {
        // Add seller-specific endpoints here
    });
});
