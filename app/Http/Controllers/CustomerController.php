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

    // ----------------- Restaurants -----------------
    public function restaurants(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->getRestaurants($request->all())]);
    }

    public function menu($id)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->getRestaurantMenu((int)$id)]);
    }

    // ----------------- Orders -----------------
    public function placeOrder(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->placeOrder($request->all())]);
    }

    public function trackOrder($id)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->trackOrder((int)$id)]);
    }

    // ----------------- Reviews -----------------
    public function review(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->submitReview($request->all())]);
    }

    public function updateReview(Request $request, $id)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->updateReview((int)$id, $request->all())]);
    }

    public function deleteReview($id)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->deleteReview((int)$id)]);
    }

    // ----------------- Profile -----------------
    public function profile()
    {
        return response()->json(['success' => true, 'data' => $this->customerService->getProfile()]);
    }

    public function updateProfile(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->updateProfile($request->all())]);
    }

    // ----------------- Addresses -----------------
    public function listAddresses()
    {
        return response()->json(['success' => true, 'data' => $this->customerService->listAddresses()]);
    }

    public function createAddress(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->createAddress($request->all())]);
    }

    public function updateAddress(Request $request, $id)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->updateAddress((int)$id, $request->all())]);
    }

    public function deleteAddress($id)
    {
        return response()->json(['success' => true, 'data' => $this->customerService->deleteAddress((int)$id)]);
    }
}
