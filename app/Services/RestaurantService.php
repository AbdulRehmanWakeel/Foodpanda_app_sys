<?php

namespace App\Services;
use Illuminate\Support\Facades\Auth;

use App\Services\Contracts\RestaurantServiceInterface;
use App\Models\Restaurant;
use App\Models\Order;
use App\Helpers\FilterPipeline;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RestaurantService implements RestaurantServiceInterface
{
    public function getRestaurants($filters = [], $perPage = 10, $request = null)
    {
        $query = Restaurant::with('menus')->where('is_verified', true);
        if ($request) {
            $query = FilterPipeline::apply($query, $filters, $request->all());
        }
        return $query->paginate($perPage);
    }


    public function getRestaurantById(int $id)
    {
        $restaurant = Restaurant::with('menus', 'orders')->find($id);

        if (!$restaurant) {
            throw new ModelNotFoundException("Restaurant not found");
        }

        return $restaurant;
    }

    public function createRestaurant(array $data)
    {
        $data['user_id'] = auth()->id();
        $data['commission_rate'] = $data['commission_rate'] ?? 15.0;
        $data['approval_status'] = $data['approval_status'] ?? 'pending';
        $data['is_verified'] = $data['is_verified'] ?? 0;
        return Restaurant::create($data);
    }


    public function getOrdersForRestaurant(array $filters = [], int $perPage = 10, $request = null)
    {
        $user = Auth::user();

        if (!$user || !$user->restaurant_id) {
            return [
                'success' => false,
                'error'   => 'Restaurant ID not found for this user.'
            ];
        }

        $restaurantId = $user->restaurant_id;

        $query = Order::where('restaurant_id', $restaurantId);

        $filterMap = [
            'earnings_date' => \App\Filters\EarningsDateFilter::class,
            'menu_category' => \App\Filters\MenuCategoryFilter::class,
            'order_date'    => \App\Filters\OrderDateFilter::class,
            'status'        => \App\Filters\OrderStatusFilter::class,
        ];

        $query = FilterPipeline::apply($query, $filters, $filterMap);

        return $query->paginate($perPage);
    }
    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, string $status)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return false;  
        }

        $order->status = $status;
        $order->save();

        return $order;  
    }
    public function getEarningsStats(int $restaurantId, array $filters = [], $request = null): array
    {
        $query = Order::where('restaurant_id', $restaurantId);
        // Apply filters if needed (date ranges, status, etc.)
        if (!empty($filters)) {
            $filterMap = [
                'status'     => \App\Filters\OrderStatusFilter::class,
                'date_from'  => \App\Filters\DateFromFilter::class,
                'date_to'    => \App\Filters\DateToFilter::class,
            ];
            $query = \App\Helpers\FilterPipeline::apply($query, $filters, $filterMap);
        }
        $totalSales   = $query->sum('total_price');
        $totalOrders  = $query->count();
        $restaurant = Restaurant::findOrFail($restaurantId);
        $commissionRate = $restaurant->commission_rate ?? 0;
        $commission = ($totalSales * $commissionRate) / 100;
        $netPayout  = $totalSales - $commission;
        return [
            'total_sales'   => $totalSales,
            'total_orders'  => $totalOrders,
            'commission'    => $commission,
            'net_payout'    => $netPayout,
        ];
    }

}
