<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Restaurant;

use App\Services\Contracts\AdminServiceInterface;

class AdminService implements AdminServiceInterface
{
    public function listUsers()
    {
        return User::role('customer', 'web')->get();  
    }


    public function listRestaurants()
    {
        return Restaurant::all();
    }

    public function listRiders()
    {
        return User::role('rider','web')->get();
    }

    public function listOrders()
    {
        return Order::with(['customer','restaurant'])->latest()->get();
    }


    public function getAnalytics()
    {
        return [
            'total_users' => User::role('customer')->count(),
            'total_restaurants' => Restaurant::count(),
            'total_riders' => User::role('rider')->count(),
            'total_orders' => Order::count(),
            'sales' => Order::sum('total_price'),
        ];
    }

}
