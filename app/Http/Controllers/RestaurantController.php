<?php

namespace App\Http\Controllers;

use App\Services\Contracts\RestaurantServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    protected $restaurantService;

    public function __construct(RestaurantServiceInterface $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

     
    public function index()
    {
        $restaurants = $this->restaurantService->getRestaurants();
        return response()->json(['success' => true, 'data' => $restaurants]);
    }

     
    public function show($id)
    {
        try {
            $restaurant = $this->restaurantService->getRestaurantById($id);
            return response()->json(['success' => true, 'data' => $restaurant]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

     
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'         => 'required|exists:users,id',
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:restaurants,email',
            'phone'           => 'required|string|max:20',
            'address'         => 'required|string|max:500',
            'cuisine_type'    => 'nullable|string|max:100',
            'delivery_radius' => 'nullable|numeric|min:1',
            'opening_time'    => 'nullable|date_format:H:i',
            'closing_time'    => 'nullable|date_format:H:i|after:opening_time',
            'is_verified'     => 'boolean'
        ]);

        $restaurant = $this->restaurantService->createRestaurant($validated);

        return response()->json([
            'success' => true,
            'data'    => $restaurant
        ], 201);
    }

     
    public function orders(Request $request)
    {
        $restaurantId = $request->get('restaurant_id');
        if (!$restaurantId && Auth::check() && Auth::user()->restaurant) {
            $restaurantId = Auth::user()->restaurant->id;
        }
        if (!$restaurantId) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant ID not found'
            ], 400);
        }
        $orders = $this->restaurantService->getOrdersForRestaurant($restaurantId);
        return response()->json([
            'success' => true,
            'data'    => $orders
        ]);
    }

     
    public function updateOrderStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,accepted,preparing,delivered,cancelled'
        ]);
        $order = $this->restaurantService->updateOrderStatus($id, $validated['status']);
        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data'    => $order
        ]);
    }

}
