<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Services\Contracts\RiderServiceInterface;
use Illuminate\Support\Facades\Hash;

class RiderService implements RiderServiceInterface
{
    // ---------------- Auth ----------------
    public function register(array $data)
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
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
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function logout()
    {
        auth('api')->logout();
        return ['message' => 'Logged out successfully'];
    }

    // ---------------- Profile ----------------
    public function getProfile($riderId)
    {
        return User::with('roles')->findOrFail($riderId);
    }

    public function updateProfile($riderId, array $data)
    {
        $rider = User::findOrFail($riderId);
        $rider->update($data);
        return $rider;
    }

    public function uploadDocuments($riderId, array $files)
    {
        $rider = User::findOrFail($riderId);

        $uploaded = [];
        foreach ($files as $key => $file) {
            $path = $file->store('riders/documents', 'public');
            $uploaded[$key] = $path;
        }

        $rider->documents = json_encode($uploaded);
        $rider->save();

        return $uploaded;
    }

    // ---------------- Status ----------------
    public function updateStatus(int $riderId, bool $isOnline)
    {
        $rider = User::findOrFail($riderId);
        $rider->update(['is_online' => $isOnline]);
        return $rider;
    }

    // ---------------- Orders ----------------
    public function assignedOrders(int $riderId)
    {
        $orders = Order::where('rider_id', $riderId)->get();

        return [
            'success'  => true,
            'message'  => $orders->isEmpty()
                ? 'No orders assigned to this rider yet'
                : 'Orders fetched successfully',
            'rider_id' => $riderId,
            'orders'   => $orders,
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

    public function orderHistory($riderId)
    {
        return Order::where('rider_id', $riderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // ---------------- Earnings ----------------
    public function earnings(int $riderId)
    {
        $rider = User::findOrFail($riderId);
        $orders = $rider->orders()->where('status', 'delivered')->get();

        $totalEarnings = $orders->sum(function ($order) {
            return $order->rider_fee > 0
                ? $order->rider_fee
                : $order->total_price * 0.15;
        });

        return [
            'total_orders'   => $orders->count(),
            'total_earnings' => $totalEarnings,
            'orders'         => $orders,
        ];
    }
}
