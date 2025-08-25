<?php

namespace App\Services;

use App\Services\Contracts\CustomerServiceInterface;
use App\Models\Restaurant;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerService implements CustomerServiceInterface
{
    public function getRestaurants(array $filters)
    {
        try {
            $query = Restaurant::query();

            if (!empty($filters['location'])) {
                $query->where('location', 'like', '%'.$filters['location'].'%');
            }

            if (!empty($filters['cuisine'])) {
                $query->where('cuisine_type', $filters['cuisine']);
            }

            return $query->with('menus')->paginate(10);
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch restaurants: " . $e->getMessage());
        }
    }

    /**
     * Get restaurant menu by restaurant ID.
     */
    public function getRestaurantMenu(int $restaurantId)
    {
        try {
            return Restaurant::with('menus')->findOrFail($restaurantId);
        } catch (ModelNotFoundException $e) {
            throw new \Exception("Restaurant not found.");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch menu: " . $e->getMessage());
        }
    }

    /**
     * Place a new order.
     */
    public function placeOrder(array $data)
    {
        try {
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
        } catch (\Exception $e) {
            throw new \Exception("Failed to place order: " . $e->getMessage());
        }
    }

    /**
     * Track an order by ID.
     */
    public function trackOrder(int $orderId)
    {
        try {
            return Order::with('items.menu')->findOrFail($orderId);
        } catch (ModelNotFoundException $e) {
            throw new \Exception("Order not found.");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch order: " . $e->getMessage());
        }
    }

    /**
     * Submit a review for a restaurant.
     */
    public function submitReview(array $data)
    {
        try {
            return Review::create([
                'user_id'       => auth()->id(),
                'restaurant_id' => $data['restaurant_id'],
                'rating'        => $data['rating'],
                'comment'       => $data['comment'] ?? null,
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to submit review: " . $e->getMessage());
        }
    }
}
