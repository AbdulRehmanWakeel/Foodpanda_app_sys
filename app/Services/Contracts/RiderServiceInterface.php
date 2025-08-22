<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface RiderServiceInterface
{
    public function register(array $data);
    public function login(array $credentials);
    public function logout();
    public function updateStatus(int $riderId, bool $isOnline);
    public function assignedOrders(int $riderId);
    public function updateOrderStatus(int $orderId, string $status);
    public function earnings(int $riderId);
}
