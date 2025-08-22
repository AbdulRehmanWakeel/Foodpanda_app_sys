<?php

namespace App\Services;

use App\Services\Contracts\RestaurantServiceInterface;
use App\Models\Restaurant;
use App\Models\Order;  
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RestaurantService implements RestaurantServiceInterface
{
    public function getRestaurants($perPage = 10)
    {
        return Restaurant::with('menus')
            ->where('is_verified', true)
            ->paginate($perPage);
    }

    public function getRestaurantById($id)
    {
        $restaurant = Restaurant::with('menus', 'orders')->find($id);  

        if (!$restaurant) {
            throw new ModelNotFoundException("Restaurant not found");
        }

        return $restaurant;
    }

    public function createRestaurant(array $data)  
    {
        return Restaurant::create($data);
    }

    public function getOrdersForRestaurant($restaurantId, $perPage = 10)
    {
        return \App\Models\Order::where('restaurant_id', $restaurantId)
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    }


    public function updateOrderStatus(int $orderId, string $status)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => $status]);
        return $order;
    }

}
