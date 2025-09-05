<?php

namespace App\Services\Contracts;

interface RiderServiceInterface
{
    // Auth
    public function register(array $data);
    public function login(array $credentials);
    public function logout();

    // Profile
    public function getProfile($riderId);
    public function updateProfile($riderId, array $data);

    // Status
    public function updateStatus(int $riderId, bool $isOnline);

    // Orders
    public function assignedOrders(int $riderId);
    public function updateOrderStatus(int $orderId, string $status);
    public function orderHistory($riderId);

    // Earnings
    public function earnings(int $riderId);
}
