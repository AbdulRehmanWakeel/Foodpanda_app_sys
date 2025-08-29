<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\RiderServiceInterface;

class RiderController extends Controller
{
    protected $riderService;

    public function __construct(RiderServiceInterface $riderService)
    {
        $this->riderService = $riderService;
    }

     
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = $this->riderService->register($data);

        return response()->json(['user' => $user], 201);
    }

     
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $this->riderService->login($credentials);

        if (!$result) {
            return response()->json(['error' => 'Invalid credentials or not a rider'], 401);
        }

        return response()->json($result);  
    }

     
    public function logout()
    {
        $this->riderService->logout();
        return response()->json(['message' => 'Logged out successfully']);
    }

     
    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'is_online' => 'required|boolean'
        ]);

        $rider = auth()->user();
        $result = $this->riderService->updateStatus($rider->id, $data['is_online']);

        return response()->json($result);
    }

     
    public function assignedOrders()
    {
        $rider = auth()->user();
        if (!$rider) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized, no rider found'
            ], 401);
        }
        $orders = $this->riderService->assignedOrders($rider->id);
        return response()->json($orders);
    }



     
    public function updateOrderStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:picked,delivered'
        ]);

        $order = $this->riderService->updateOrderStatus($id, $data['status']);
        return response()->json($order);
    }

     
    public function earnings()
    {
        $rider = auth()->user();
        $earnings = $this->riderService->earnings($rider->id);
        return response()->json(['earnings' => $earnings]);
    }

}
