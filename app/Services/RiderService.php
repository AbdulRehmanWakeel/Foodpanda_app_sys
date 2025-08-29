<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Services\Contracts\RiderServiceInterface;
use Illuminate\Support\Facades\Hash;

class RiderService implements RiderServiceInterface
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('rider');

        return $user;  
    }

    public function login(array $credentials)
    {
        if (!$token = auth('api')->attempt($credentials)) {
            return false;
        }

        $user = auth('api')->user();

        if (!$user->hasRole('rider')) {
            return false;
        }

        return [
            'user' => $user,
            'token' => $token  
        ];
    }

    public function logout()
    {
        auth('api')->logout();
        return ['message' => 'Logged out successfully'];
    }

    public function updateStatus(int $riderId, bool $isOnline)
    {
        $rider = User::findOrFail($riderId);
        $rider->update(['is_online' => $isOnline]);
        return $rider;
    }

    public function assignedOrders(int $riderId)
    {
        $orders = Order::where('rider_id', $riderId)->get();
        if ($orders->isEmpty()) {
            return [
                'success' => true,
                'message' => 'No orders assigned to this rider yet',
                'rider_id' => $riderId,
                'orders' => []
            ];
        }
        return [
            'success' => true,
            'message' => 'Orders fetched successfully',
            'rider_id' => $riderId,
            'orders' => $orders
        ];
    }

    public function updateOrderStatus(int $orderId, string $status)
    {
        if (!in_array($status, ['picked', 'delivered'])) {
            throw new \InvalidArgumentException('Invalid order status for rider.');
        }

        $order = Order::findOrFail($orderId);
        $order->update(['status' => $status]);
        return $order;
    }

    public function earnings(int $riderId)
    {
        $rider = User::findOrFail($riderId);
        $orders = $rider->orders()->whereIn('status', ['delivered'])->get();
        // Sum rider_fee if set, otherwise calculate 15% of total_price
        return $orders->sum(function($order) {
            return $order->rider_fee > 0 ? $order->rider_fee : $order->total_price * 0.15;
        });
    }


}
