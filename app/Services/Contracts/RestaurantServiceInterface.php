<?php

namespace App\Services\Contracts;

interface RestaurantServiceInterface
{
    public function getRestaurants($perPage = 10);
    public function getRestaurantById($id);
    public function createRestaurant(array $data);
    public function getOrdersForRestaurant($restaurantId, $perPage = 10);
    public function updateOrderStatus(int $orderId, string $status);
}
