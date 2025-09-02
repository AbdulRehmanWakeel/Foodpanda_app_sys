<?php

namespace App\Http\Controllers;

use App\Services\Contracts\CustomerServiceInterface;
use App\Services\ErrorService;
use Illuminate\Http\Request;
use Throwable;

class CustomerController extends Controller
{
    protected CustomerServiceInterface $service; 
    protected ErrorService $errorService;

    public function __construct(CustomerServiceInterface $service, ErrorService $errorService)
    {
        $this->service = $service;        
        $this->errorService = $errorService;
    }

    /**
     * Wraps controller actions to catch errors and log them.
     */
    private function handleRequest(callable $callback, ?Request $request = null)
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            // Log the error
            $this->errorService->log($exception, $request);

            // Return JSON error response
            $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage()
            ], $status);
        }
    }

    // ----------------- Restaurants -----------------
    public function restaurants(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->getRestaurants($request->all())
        ]), $request);
    }

    public function menu($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->getRestaurantMenu((int)$id)
        ]));
    }

    // ----------------- Orders -----------------
    public function placeOrder(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->placeOrder($request->all())
        ]), $request);
    }

    public function trackOrder($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->trackOrder((int)$id)
        ]));
    }

    public function orderHistory(Request $request)
    {
        $filters = $request->only(['status']);
        $perPage = $request->get('per_page', 10);

        $orders = $this->service->getOrderHistory($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    public function reorder($orderId)
    {
        try {
            $newOrder = $this->service->reorder($orderId);

            return response()->json([
                'success' => true,
                'message' => 'Order reordered successfully',
                'data'    => $newOrder
            ]);
        } catch (\Exception $e) {
            return $this->errorService->handle($e);
        }
    }

    // ----------------- Reviews -----------------
    public function review(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->submitReview($request->all())
        ]), $request);
    }

    public function updateReview(Request $request, $id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->updateReview((int)$id, $request->all())
        ]), $request);
    }

    public function deleteReview($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->deleteReview((int)$id)
        ]));
    }

    // ----------------- Profile -----------------
    public function profile()
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->getProfile()
        ]));
    }

    public function updateProfile(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->updateProfile($request->all())
        ]), $request);
    }

    // ----------------- Addresses -----------------
    public function listAddresses()
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->listAddresses()
        ]));
    }

    public function createAddress(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->createAddress($request->all())
        ]), $request);
    }

    public function updateAddress(Request $request, $id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->updateAddress((int)$id, $request->all())
        ]), $request);
    }

    public function deleteAddress($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->service->deleteAddress((int)$id)
        ]));
    }
}
