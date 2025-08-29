<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\Contracts\AdminServiceInterface;
use App\Helpers\FilterPipeline;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminService implements AdminServiceInterface
{
     
    public function listUsers(array $filters = [])
    {
         
        $query = User::query();

         
        return FilterPipeline::apply($query, $filters, User::getFilterMap())->get();
    }

    public function listRestaurants(array $filters = [])
    {
        $query = Restaurant::query();
        if (method_exists(Restaurant::class, 'getFilterMap')) {
            return FilterPipeline::apply($query, $filters, Restaurant::getFilterMap())->get();
        }
        return $query->get();
    }

    public function listRiders(array $filters = [])
    {
        $query = User::role('rider');
        return FilterPipeline::apply($query, $filters, User::getFilterMap())->get();
    }

    public function listOrders(array $filters = [])
    {
        $query = Order::with(['customer', 'restaurant', 'rider'])->latest();
        if (method_exists(Order::class, 'getFilterMap')) {
            return FilterPipeline::apply($query, $filters, Order::getFilterMap())->get();
        }
        return $query->get();
    }

    public function getAnalytics()
    {
        return [
            'total_users' => User::count(),
            'total_restaurants' => Restaurant::count(),
            'total_riders' => User::role('rider')->count(),
            'total_orders' => Order::count(),
            'sales' => Order::sum('total_price'),
        ];
    }

    // ---------- USERS ----------
    public function createUser(array $data)
    {
        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Password is required');
        }
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

         
        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        } else {
            $user->assignRole('customer');
        }

        return $user;
    }

    public function updateUser(int $id, array $data)
    {
        $user = User::findOrFail($id);
        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        // Update user
        $user->update($data);
        // Update role if provided
        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }
        // Return fresh instance
        return $user->fresh();
    }


    public function deleteUser(int $id)
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }

    // ---------- RESTAURANTS ----------
    public function createRestaurant(array $data)
    {
        return Restaurant::create($data);
    }

    public function updateRestaurant(int $id, array $data)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update($data);
        return $restaurant;
    }

    public function deleteRestaurant(int $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return $restaurant->delete();
    }

    public function approveRestaurant(int $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->approval_status = 'approved';
        $restaurant->save();
        return $restaurant;
    }


    public function rejectRestaurant(int $id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->approval_status = 'rejected';
        $restaurant->save();
        return $restaurant;
    }

    // ---------- RIDERS ----------
    public function createRider(array $data)
    {
        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Password is required');
        }
        // Create user with rider-specific fields
        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'],
            'password'      => Hash::make($data['password']),
            'vehicle_type'  => $data['vehicle_type'] ?? null,
            'rider_license' => $data['rider_license'] ?? null,
            'status'        => 'pending',  
        ]);
        // Assign role
        $user->assignRole('rider');
        return $user;
    }

    public function updateRider(int $id, array $data)
    {
        // Find user by ID
        $rider = User::findOrFail($id);
        // Check if user really has the rider role
        if (!$rider->hasRole('rider')) {
            throw new \Exception("User with ID {$id} is not a rider.");
        }
        // Handle password update
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $rider->update($data);
        return $rider;
    }


    public function deleteRider(int $id)
    {
        $rider = User::role('rider')->findOrFail($id);
        return $rider->delete();
    }

    public function verifyRider(int $id)
    {
        $rider = User::find($id);
        // ensure user exists AND has rider role
        if (!$rider || !$rider->hasRole('rider')) {
            return null;
        }
        $rider->verification_status = 'verified';
        $rider->verified_at = now();
        $rider->save();
        return $rider;
    }
    public function rejectRider(int $id): ?User
    {
        $rider = User::find($id);
        if (! $rider || ! $rider->hasRole('rider')) {
            return null;
        }
        $rider->verification_status = 'rejected';
        $rider->save();
        return $rider;
    }


}
