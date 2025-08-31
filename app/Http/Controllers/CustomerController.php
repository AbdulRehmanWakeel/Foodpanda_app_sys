<?php

namespace App\Http\Controllers;

use App\Services\Contracts\CustomerServiceInterface;
use App\Services\ErrorService;
use Illuminate\Http\Request;
use Throwable;

class CustomerController extends Controller
{
    protected $customerService;
    protected $errorService;

    public function __construct(CustomerServiceInterface $customerService, ErrorService $errorService)
    {
        $this->customerService = $customerService;
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
            'data' => $this->customerService->getRestaurants($request->all())
        ]), $request);
    }

    public function menu($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->getRestaurantMenu((int)$id)
        ]));
    }

    // ----------------- Orders -----------------
    public function placeOrder(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->placeOrder($request->all())
        ]), $request);
    }

    public function trackOrder($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->trackOrder((int)$id)
        ]));
    }

    // ----------------- Reviews -----------------
    public function review(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->submitReview($request->all())
        ]), $request);
    }

    public function updateReview(Request $request, $id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->updateReview((int)$id, $request->all())
        ]), $request);
    }

    public function deleteReview($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->deleteReview((int)$id)
        ]));
    }

    // ----------------- Profile -----------------
    public function profile()
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->getProfile()
        ]));
    }

    public function updateProfile(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->updateProfile($request->all())
        ]), $request);
    }

    // ----------------- Addresses -----------------
    public function listAddresses()
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->listAddresses()
        ]));
    }

    public function createAddress(Request $request)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->createAddress($request->all())
        ]), $request);
    }

    public function updateAddress(Request $request, $id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->updateAddress((int)$id, $request->all())
        ]), $request);
    }

    public function deleteAddress($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->customerService->deleteAddress((int)$id)
        ]));
    }
}
