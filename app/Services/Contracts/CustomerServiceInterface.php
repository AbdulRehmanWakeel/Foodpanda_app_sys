<?php

namespace App\Services\Contracts;

interface CustomerServiceInterface
{
    // Restaurants & Menu
    public function getRestaurants(array $filters);
    public function getRestaurantMenu(int $restaurantId);

    // Orders
    public function placeOrder(array $data);
    public function trackOrder(int $orderId);
    public function getOrderHistory(array $filters = [], int $perPage = 10);
    public function reorder(int $orderId);


    // Reviews
    public function submitReview(array $data);
    public function updateReview(int $id, array $data);
    public function deleteReview(int $id);

    // Profile
    public function getProfile();
    public function updateProfile(array $data);

    // Addresses
    public function listAddresses();
    public function createAddress(array $data);
    public function updateAddress(int $id, array $data);
    public function deleteAddress(int $id);
}