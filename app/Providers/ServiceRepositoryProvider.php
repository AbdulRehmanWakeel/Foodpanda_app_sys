<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\RestaurantServiceInterface;
use App\Services\RestaurantService;
use App\Services\Contracts\MenuServiceInterface;
use App\Services\MenuService;

class ServiceRepositoryProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->app->bind(RestaurantServiceInterface::class, RestaurantService::class);
        $this->app->bind(MenuServiceInterface::class, MenuService::class);
    }

    public function boot()
    {
        //
    }
}
 