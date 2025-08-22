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
            return collect([
                [
                    'id' => 0,
                    'restaurant_id' => null,
                    'user_id' => null,
                    'rider_id' => $riderId,
                    'total_price' => 0,
                    'status' => 'no_orders',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
        return $orders;
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
        return $rider->orders()->sum('rider_fee');
    }
}
