<?php

namespace App\Providers;

use App\Interfaces\AdminOrderInterface;
use App\Interfaces\AdminProductInterface;
use App\Interfaces\SellerAuthInterface;
use App\Interfaces\SellerOrderInterface;
use App\Interfaces\SellerProductInterface;
use App\Interfaces\SellerProfileInterface;
use App\Services\Admin\AdminOrderService;
use App\Services\Admin\AdminProductService;
use App\Services\Seller\SellerOrderService;
use App\Services\Seller\SellerProductService;
use App\Services\Seller\SellerProfileService;
use App\Services\SellerAuthService;
use Illuminate\Support\ServiceProvider;

// Interfaces
use App\Interfaces\AdminAuthInterface;
use App\Interfaces\AdminUserInterface;

// Services
use App\Services\Admin\AdminAuthService;
use App\Services\Admin\AdminUserService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Admin interfaces to implementations
        $this->app->bind(AdminAuthInterface::class, AdminAuthService::class);
        $this->app->bind(AdminUserInterface::class, AdminUserService::class);
        $this->app->bind(\App\Interfaces\AdminSellerInterface::class, \App\Services\Admin\AdminSellerService::class);
        $this->app->bind(AdminProductInterface::class, AdminProductService::class);
        $this->app->bind(AdminOrderInterface::class, AdminOrderService::class);
        $this->app->bind(SellerAuthInterface::class, SellerAuthService::class);


        $this->app->bind(SellerProfileInterface::class, SellerProfileService::class);
        $this->app->bind(SellerProductInterface::class, SellerProductService::class);
        $this->app->bind(SellerOrderInterface::class, SellerOrderService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
