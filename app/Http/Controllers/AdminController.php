<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AdminServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    private AdminServiceInterface $adminService;

    public function __construct(AdminServiceInterface $adminService)
    {
        $this->adminService = $adminService;
         
    }

     
    public function users(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->adminService->listUsers($request->all())]);
    }

    public function restaurants(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->adminService->listRestaurants($request->all())]);
    }

    public function riders(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->adminService->listRiders($request->all())]);
    }

    public function orders(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->adminService->listOrders($request->all())]);
    }

    public function analytics(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->adminService->getAnalytics()]);
    }

     
    public function storeUser(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string'
        ]);
        $user = $this->adminService->createUser($payload);
        return response()->json(['success' => true, 'data' => $user], 201);
    }

    public function updateUser(Request $request, $id): JsonResponse
    {
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'vehicle_type' => 'nullable|string|max:50',    
            'rider_license' => 'nullable|string|max:100',  
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|string|in:admin,restaurant,rider,customer',
        ]);
        // Extract only the fields we want to update
        $payload = $request->only([
            'name', 
            'email', 
            'phone', 
            'vehicle_type', 
            'rider_license', 
            'password', 
            'role'
        ]);
        try {
            // Call service to update
            $user = $this->adminService->updateUser((int)$id, $payload);
            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function deleteUser($id): JsonResponse
    {
        $this->adminService->deleteUser((int)$id);
        return response()->json(['success' => true, 'message' => 'User deleted']);
    }

    // ---------- RESTAURANTS CRUD & APPROVAL ----------
    public function storeRestaurant(Request $request): JsonResponse
    {
        // Validate incoming request
        $payload = $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
            'cuisine_type' => 'nullable|string',
            'email'        => 'required|email|unique:restaurants,email',  
        ]);
        // Add the currently authenticated user's ID
        $payload['user_id'] = auth()->id(); // make sure the user is logged in
        // Create the restaurant
        $restaurant = $this->adminService->createRestaurant($payload);
        return response()->json([
            'success' => true,
            'message' => 'Restaurant created successfully',
            'data' => $restaurant
        ]);
    }


    public function updateRestaurant(Request $request, $id): JsonResponse
    {
        $payload = $request->all();
        $restaurant = $this->adminService->updateRestaurant((int)$id, $payload);
        return response()->json(['success' => true, 'data' => $restaurant]);
    }

    public function deleteRestaurant($id): JsonResponse
    {
        $this->adminService->deleteRestaurant((int)$id);
        return response()->json(['success' => true, 'message' => 'Restaurant deleted']);
    }

    public function approveRestaurant($id): JsonResponse
    {
        $restaurant = $this->adminService->approveRestaurant((int)$id);
        return response()->json(['success' => true, 'data' => $restaurant]);
    }

    public function rejectRestaurant($id): JsonResponse
    {
        $restaurant = $this->adminService->rejectRestaurant((int)$id);
        return response()->json(['success' => true, 'data' => $restaurant]);
    }

    // ---------- RIDERS CRUD & VERIFY ----------
    public function storeRider(Request $request): JsonResponse
    {
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
    }

    public function updateRider(Request $request, $id): JsonResponse
    {
        $payload = $request->all();
        try {
            $rider = $this->adminService->updateRider((int) $id, $payload);
            return response()->json([
                'success' => true,
                'data' => $rider
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);  
        }
    }

    public function deleteRider($id): JsonResponse
    {
        $this->adminService->deleteRider((int)$id);
        return response()->json(['success' => true, 'message' => 'Rider deleted']);
    }

    public function verifyRider($id): JsonResponse
    {
        $rider = $this->adminService->verifyRider((int)$id);
        if (!$rider) {
            return response()->json([
                'success' => false,
                'message' => 'Rider not found or not assigned rider role'
            ], 404);
        }
        return response()->json(['success' => true, 'data' => $rider]);
    }


    public function rejectRider($id): JsonResponse
    {
        $rider = $this->adminService->rejectRider((int) $id);
        if (! $rider) {
            return response()->json([
                'success' => false,
                'message' => 'Rider not found or invalid role'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $rider
        ]);
    }


}
