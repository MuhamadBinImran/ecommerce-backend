<?php

namespace App\Providers;

use App\Interfaces\AdminProductInterface;
use App\Services\Admin\AdminProductService;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
