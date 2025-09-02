<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\RestaurantServiceInterface;
use App\Services\RestaurantService;
use App\Services\Contracts\MenuServiceInterface;
use App\Services\MenuService;
use App\Services\Contracts\PromotionServiceInterface;
use App\Services\PromotionService;


class ServiceRepositoryProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->app->bind(RestaurantServiceInterface::class, RestaurantService::class);
        $this->app->bind(MenuServiceInterface::class, MenuService::class);
        $this->app->bind(PromotionServiceInterface::class, PromotionService::class);


    }

    public function boot()
    {
        //
    }
}
 