<?php

namespace App\Services;

use App\Services\Contracts\CustomerServiceInterface;
use App\Models\Restaurant;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Helpers\FilterPipeline;
use Illuminate\Support\Facades\DB;   // <-- add this

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
    public function getOrderHistory(array $filters = [], int $perPage = 10)
    {
        $query = Order::with(['items.menu', 'restaurant', 'rider'])
                      ->where('user_id', auth()->id());

        // Optional status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform data for frontend
        $orders->getCollection()->transform(function($order) {
            return [
                'id' => $order->id,
                'restaurant_id' => $order->restaurant_id,
                'restaurant_name' => $order->restaurant->name ?? null,
                'user_id' => $order->user_id,
                'total_price' => $order->total_price,
                'rider_fee' => $order->rider_fee,
                'status' => $order->status,
                'rider_id' => $order->rider_id,
                'rider_name' => $order->rider->name ?? null,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                'items' => $order->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'menu_id' => $item->menu_id,
                        'menu_name' => $item->menu->name ?? null,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'addons' => $item->addons,
                    ];
                }),
            ];
        });

        return $orders;
    }
    public function reorder(int $orderId)
    {
        return DB::transaction(function () use ($orderId) {
            $oldOrder = Order::with('items.menu')->findOrFail($orderId);

            // Create new order
            $newOrder = Order::create([
                'restaurant_id' => $oldOrder->restaurant_id,
                'user_id'       => auth()->id(),
                'total_price'   => $oldOrder->total_price,
                'rider_fee'     => $oldOrder->rider_fee,
                'status'        => 'pending',
            ]);

            // Copy items
            foreach ($oldOrder->items as $item) {
                OrderItem::create([
                    'order_id'   => $newOrder->id,
                    'menu_id'    => $item->menu_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->price,
                    'addons'     => $item->addons,
                ]);
            }

            return $newOrder->load('items.menu', 'restaurant');
        });
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