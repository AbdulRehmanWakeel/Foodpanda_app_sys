<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Contracts\RestaurantServiceInterface;

class RestaurantController extends Controller
{
    protected $restaurantService;

    public function __construct(RestaurantServiceInterface $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $restaurants = $this->restaurantService->getRestaurants($filters, 10, $request);

        return response()->json([
            'success' => true,
            'data'    => $restaurants,
        ]);
    }

    /**
     * Show a single restaurant by ID
     */
    public function show($id)
    {
        try {
            $restaurant = $this->restaurantService->getRestaurantById($id);

            return response()->json([
                'success' => true,
                'data'    => $restaurant,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create a new restaurant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:restaurants,email',
            'phone'           => 'required|string|max:20',
            'address'         => 'required|string|max:255',
            'cuisine_type'    => 'nullable|string|max:100',
            'delivery_radius' => 'nullable|numeric',
            'opening_time'    => 'nullable|date_format:H:i',
            'closing_time'    => 'nullable|date_format:H:i',
        ]);
        $restaurant = $this->restaurantService->createRestaurant($validated);
        return response()->json([
            'success' => true,
            'data'    => $restaurant
        ], 201);
    }

    public function orders(Request $request)
    {
        $restaurantId = $request->query('restaurant_id');

        if (!$restaurantId) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant ID not found',
            ]);
        }

        $filters = $request->only(['earnings_date', 'menu_category', 'order_date', 'status']);
        $perPage = $request->query('per_page', 10);

        $orders = $this->restaurantService->getOrdersForRestaurant($restaurantId, $filters, $perPage, $request);

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,preparing,delivered,canceled'
        ]);

        $status = $request->input('status');

        $updatedOrder = $this->restaurantService->updateOrderStatus((int)$id, $status);

        if (!$updatedOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Order ID not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $updatedOrder
        ]);
    }
    /**
     * Get earnings stats
     */
    public function stats(Request $request, $id)
    {
        return response()->json(
            $this->restaurantService->getEarningsStats($id, $request->all(), $request)
        );
    }

}