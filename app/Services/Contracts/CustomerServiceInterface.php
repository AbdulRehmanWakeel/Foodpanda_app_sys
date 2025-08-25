<?php

namespace App\Services\Contracts;

interface CustomerServiceInterface
{
    public function getRestaurants(array $filters);

     
    public function getRestaurantMenu(int $restaurantId);

     
    public function placeOrder(array $data);

     
    public function trackOrder(int $orderId);

     
    public function submitReview(array $data);
}

