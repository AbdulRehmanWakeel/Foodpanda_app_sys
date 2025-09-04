<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\AuthService;
use App\Services\Contracts\AdminServiceInterface;
use App\Services\AdminService;
use App\Services\RiderService;
use App\Services\Contracts\RiderServiceInterface;
use App\Services\Contracts\CustomerServiceInterface;
use App\Services\CustomerService;
use App\Services\Contracts\CartServiceInterface;
use App\Services\CartService;
use App\Services\Contracts\CartItemServiceInterface;
use App\Services\CartItemService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AuthServiceInterface::class,
            AuthService::class
        );

        $this->app->bind(
            AdminServiceInterface::class,
            AdminService::class
        );
        $this->app->bind(
            RiderServiceInterface::class,
            RiderService::class
        );
        $this->app->bind(
            CustomerServiceInterface::class,
            CustomerService::class
        );
        $this->app->bind(
            CartServiceInterface::class, 
            CartService::class
        );
        $this->app->bind(
            CartItemServiceInterface::class,
            CartItemService::class
        );

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
