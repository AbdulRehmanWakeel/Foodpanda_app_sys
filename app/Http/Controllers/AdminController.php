<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AdminServiceInterface;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    private AdminServiceInterface $adminService;

    public function __construct(AdminServiceInterface $adminService)
    {
        $this->adminService = $adminService;

    }

     
    public function users()
    {
        $users = $this->adminService->listUsers();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

     
    public function restaurants(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->adminService->listRestaurants()
        ]);
    }

    public function riders(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->adminService->listRiders()
        ]);
    }

     
    public function orders(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->adminService->listOrders()
        ]);
    }

     
    public function analytics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->adminService->getAnalytics()
        ]);
    }

}
