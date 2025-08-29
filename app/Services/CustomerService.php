<?php

namespace App\Services;

use App\Services\Contracts\CustomerServiceInterface;
use App\Models\Restaurant;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Review;
use App\Helpers\FilterPipeline;
use Illuminate\Support\Facades\Hash;

class CustomerService implements CustomerServiceInterface
{
    // ----------------- Restaurants & Menus -----------------
    public function getRestaurants(array $filters)
    {
        $query = Restaurant::query();
        return FilterPipeline::apply($query, $filters, Restaurant::getFilterMap())
            ->with('menus')
            ->paginate(10);
    }

    public function getRestaurantMenu(int $restaurantId)
    {
        return Restaurant::with('menus')->findOrFail($restaurantId);
    }

    // ----------------- Orders -----------------
    public function placeOrder(array $data)
    {
        $order = Order::create([
            'user_id'       => auth()->id(),
            'restaurant_id' => $data['restaurant_id'],
            'total_price'   => 0,
            'status'        => 'pending',
        ]);

        $totalPrice = 0;
        foreach ($data['items'] as $item) {
            $menu = Menu::findOrFail($item['menu_id']);
            $price = $menu->price * $item['quantity'];
            $order->items()->create([
                'menu_id'  => $menu->id,
                'quantity' => $item['quantity'],
                'price'    => $price,
            ]);
            $totalPrice += $price;
        }

        $order->update(['total_price' => $totalPrice]);
        return $order->load('items.menu');
    }

    public function trackOrder(int $orderId)
    {
        return Order::with('items.menu')->findOrFail($orderId);
    }

    // ----------------- Reviews -----------------
    public function submitReview(array $data)
    {
        return Review::create([
            'user_id'       => auth()->id(),
            'restaurant_id' => $data['restaurant_id'],
            'rating'        => $data['rating'],
            'comment'       => $data['comment'] ?? null,
        ]);
    }

    public function updateReview(int $id, array $data)
    {
        $review = auth()->user()->reviews()->findOrFail($id);
        $review->update($data);
        return $review->fresh();
    }

    public function deleteReview(int $id)
    {
        $review = auth()->user()->reviews()->findOrFail($id);
        return $review->delete();
    }

    // ----------------- Profile -----------------
    public function getProfile()
    {
        return auth()->user();
    }

    public function updateProfile(array $data)
    {
        $user = auth()->user();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return $user->fresh();
    }

    // ----------------- Addresses -----------------
    public function listAddresses()
    {
        return auth()->user()->addresses()->get();
    }

    public function createAddress(array $data)
    {
        return auth()->user()->addresses()->create($data);
    }

    public function updateAddress(int $id, array $data)
    {
        $address = auth()->user()->addresses()->findOrFail($id);
        $address->update($data);
        return $address->fresh();
    }

    public function deleteAddress(int $id)
    {
        $address = auth()->user()->addresses()->findOrFail($id);
        return $address->delete();
    }
}
