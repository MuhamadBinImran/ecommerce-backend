<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController; // Unified controller for all roles
use App\Http\Controllers\Admin\AdminUserController;

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

        // Customer management
        Route::get('customers', [AdminUserController::class, 'index']);
        Route::get('customers/{id}', [AdminUserController::class, 'show']);
        Route::patch('customers/{id}/block', [AdminUserController::class, 'blockCustomer']);
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
