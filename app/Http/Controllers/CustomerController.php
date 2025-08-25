<?php

namespace App\Http\Controllers;

use App\Services\Contracts\CustomerServiceInterface;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerServiceInterface $customerService)
    {
        $this->customerService = $customerService;
    }

    
    public function restaurants(Request $request)
    {
        try {
            $restaurants = $this->customerService->getRestaurants($request->all());
            return response()->json(['success' => true, 'data' => $restaurants], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

     
    public function menu($id)
    {
        try {
            $restaurantId = (int) $id;   
            $menu = $this->customerService->getRestaurantMenu($restaurantId);
            return response()->json(['success' => true, 'data' => $menu], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }


     
    public function placeOrder(Request $request)
    {
        try {
            $order = $this->customerService->placeOrder($request->all());
            return response()->json(['success' => true, 'data' => $order], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

     
    public function trackOrder($id)
    {
        try {
            $order = $this->customerService->trackOrder((int)$id);
            return response()->json(['success' => true, 'data' => $order], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

     
    public function review(Request $request)
    {
        try {
            $review = $this->customerService->submitReview($request->all());
            return response()->json(['success' => true, 'data' => $review], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
