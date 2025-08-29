<?php

namespace App\Services\Contracts;

interface RestaurantServiceInterface
{
    public function getRestaurants($filters = [], $perPage = 10);
    public function getRestaurantById(int $id);
    public function createRestaurant(array $data);
    public function getOrdersForRestaurant(int $restaurantId, $filters = [], $perPage = 10, $request = null);
    public function updateOrderStatus(int $orderId, string $status);  
    public function getEarningsStats(int $restaurantId, array $filters = [], $request = null);
}
