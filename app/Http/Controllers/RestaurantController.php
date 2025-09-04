<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\RestaurantServiceInterface;
use App\Services\ErrorService;
use Throwable;

class RestaurantController extends Controller
{
    protected $restaurantService;
    protected $errorService;

    public function __construct(RestaurantServiceInterface $restaurantService, ErrorService $errorService)
    {
        $this->restaurantService = $restaurantService;
        $this->errorService = $errorService;
    }

    /**
     * Generic request handler to log errors
     */
    private function handleRequest(callable $callback, ?Request $request = null)
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            $this->errorService->log($exception, $request);

            $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

            return response()->json([
                'success' => false,
                'error' => $exception->getMessage()
            ], $status);
        }
    }

    // ---------------- Restaurants ----------------
    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $filters = $request->only(['name', 'cuisine_type', 'is_available']);
            $perPage = $request->query('per_page', 10);

            $restaurants = $this->restaurantService->getRestaurants($filters, $perPage, $request);

            return response()->json([
                'success' => true,
                'data' => $restaurants->items(),
                'meta' => [
                    'current_page' => $restaurants->currentPage(),
                    'last_page' => $restaurants->lastPage(),
                    'per_page' => $restaurants->perPage(),
                    'total' => $restaurants->total(),
                ],
            ]);
        }, $request);
    }

    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $restaurant = $this->restaurantService->getRestaurantById($id);

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $restaurant
            ]);
        });
    }

    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:restaurants,email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'cuisine_type' => 'nullable|string|max:100',
                'delivery_radius' => 'nullable|numeric',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
            ]);

            $restaurant = $this->restaurantService->createRestaurant($validated);

            return response()->json([
                'success' => true,
                'data' => $restaurant
            ], 201);
        }, $request);
    }

    // ---------------- Orders ----------------
    public function orders(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $filters = $request->only(['earnings_date', 'menu_category', 'order_date', 'status']);
            $perPage = $request->query('per_page', 10);
            $orders = $this->restaurantService->getOrdersForRestaurant($filters, $perPage, $request);
            if (isset($orders['success']) && $orders['success'] === false) {
                return response()->json($orders, 400);
            }
            return response()->json([
                'success' => true,
                'data'    => $orders
            ]);
        }, $request);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $request->validate([
                'status' => 'required|string|in:pending,preparing,delivered,canceled'
            ]);

            $status = $request->input('status');
            $updatedOrder = $this->restaurantService->updateOrderStatus((int)$id, $status);

            if (!$updatedOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $updatedOrder
            ]);
        }, $request);
    }


    // ---------------- Earnings stats ----------------
    public function stats(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $stats = $this->restaurantService->getEarningsStats($id, $request->all(), $request);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        }, $request);
    }
}