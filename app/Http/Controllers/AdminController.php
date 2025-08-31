<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AdminServiceInterface;
use App\Services\ErrorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class AdminController extends Controller
{
    private AdminServiceInterface $adminService;
    private ErrorService $errorService;

    public function __construct(AdminServiceInterface $adminService, ErrorService $errorService)
    {
        $this->adminService = $adminService;
        $this->errorService = $errorService;
    }

    /**
     * Handles requests and logs errors
     */
    private function handleRequest(callable $callback, ?Request $request = null)
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            // Log the error
            $this->errorService->log($exception, $request);

            $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage()
            ], $status);
        }
    }

    // ----------------- USERS -----------------
    public function users(Request $request): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->listUsers($request->all())
        ]), $request);
    }

    public function restaurants(Request $request): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->listRestaurants($request->all())
        ]), $request);
    }

    public function riders(Request $request): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->listRiders($request->all())
        ]), $request);
    }

    public function orders(Request $request): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->listOrders($request->all())
        ]), $request);
    }

    public function analytics(): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->getAnalytics()
        ]));
    }

    // ----------------- USER CRUD -----------------
    public function storeUser(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $payload = $request->validate([
                'name' => 'required|string|max:191',
                'email' => 'required|email|unique:users',
                'phone' => 'nullable|string',
                'password' => 'required|string|min:6',
                'role' => 'nullable|string'
            ]);
            $user = $this->adminService->createUser($payload);
            return response()->json(['success' => true, 'data' => $user], 201);
        }, $request);
    }

    public function updateUser(Request $request, $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'vehicle_type' => 'nullable|string|max:50',
                'rider_license' => 'nullable|string|max:100',
                'password' => 'nullable|string|min:6',
                'role' => 'nullable|string|in:admin,restaurant,rider,customer',
            ]);

            $payload = $request->only([
                'name', 'email', 'phone', 'vehicle_type', 'rider_license', 'password', 'role'
            ]);

            $user = $this->adminService->updateUser((int)$id, $payload);
            return response()->json(['success' => true, 'data' => $user]);
        }, $request);
    }

    public function deleteUser($id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'message' => $this->adminService->deleteUser((int)$id)
        ]));
    }

    // ----------------- RESTAURANT CRUD -----------------
    public function storeRestaurant(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $payload = $request->validate([
                'name' => 'required|string',
                'address' => 'required|string',
                'cuisine_type' => 'nullable|string',
                'email' => 'required|email|unique:restaurants,email',
            ]);

            $payload['user_id'] = auth()->id();
            $restaurant = $this->adminService->createRestaurant($payload);
            return response()->json([
                'success' => true,
                'message' => 'Restaurant created successfully',
                'data' => $restaurant
            ]);
        }, $request);
    }

    public function updateRestaurant(Request $request, $id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->updateRestaurant((int)$id, $request->all())
        ]), $request);
    }

    public function deleteRestaurant($id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'message' => 'Restaurant deleted',
            'data' => $this->adminService->deleteRestaurant((int)$id)
        ]));
    }

    public function approveRestaurant($id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->approveRestaurant((int)$id)
        ]));
    }

    public function rejectRestaurant($id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->rejectRestaurant((int)$id)
        ]));
    }

    // ----------------- RIDER CRUD -----------------
    public function storeRider(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $payload = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'phone' => 'required|string',
                'password' => 'required|string|min:6',
                'vehicle_type' => 'nullable|string',
                'rider_license' => 'nullable|string'
            ]);
            $rider = $this->adminService->createRider($payload);
            return response()->json(['success' => true, 'data' => $rider], 201);
        }, $request);
    }

    public function updateRider(Request $request, $id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->updateRider((int)$id, $request->all())
        ]), $request);
    }

    public function deleteRider($id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'message' => 'Rider deleted',
            'data' => $this->adminService->deleteRider((int)$id)
        ]));
    }

    public function verifyRider($id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->verifyRider((int)$id)
        ]));
    }

    public function rejectRider($id): JsonResponse
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data' => $this->adminService->rejectRider((int)$id)
        ]));
    }
}
