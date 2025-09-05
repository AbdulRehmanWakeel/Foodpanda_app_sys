<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\RiderServiceInterface;
use App\Services\ErrorService;
use Throwable;

class RiderController extends Controller
{
    protected $riderService;
    protected $errorService;

    public function __construct(RiderServiceInterface $riderService, ErrorService $errorService)
    {
        $this->riderService = $riderService;
        $this->errorService = $errorService;
    }

    /**
     * Generic request handler with error logging
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
                'error'   => $exception->getMessage()
            ], $status);
        }
    }

    // ---------------- Rider Auth ----------------
    public function register(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $request->validate([
                'name'     => 'required|string|max:191',
                'email'    => 'required|email|unique:users',
                'phone'    => 'required|unique:users',
                'password' => 'required|min:6',
            ]);

            $user = $this->riderService->register($data);

            return response()->json(['success' => true, 'data' => $user], 201);
        }, $request);
    }

    public function login(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $credentials = $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $result = $this->riderService->login($credentials);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials or not a rider'
                ], 401);
            }

            return response()->json(['success' => true, 'data' => $result]);
        }, $request);
    }

    public function logout()
    {
        return $this->handleRequest(function () {
            $this->riderService->logout();
            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        });
    }

    // ---------------- Profile ----------------
    public function profile()
    {
        return $this->handleRequest(function () {
            $rider = auth()->user();
            $profile = $this->riderService->getProfile($rider->id);

            return response()->json(['success' => true, 'data' => $profile]);
        });
    }

    public function updateProfile(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $rider = auth()->user();
            $updated = $this->riderService->updateProfile($rider->id, $request->all());

            return response()->json(['success' => true, 'data' => $updated]);
        }, $request);
    }


    // ---------------- Status ----------------
    public function updateStatus(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $data = $request->validate([
                'is_online' => 'required|boolean'
            ]);

            $rider = auth()->user();
            $result = $this->riderService->updateStatus($rider->id, $data['is_online']);

            return response()->json(['success' => true, 'data' => $result]);
        }, $request);
    }

    // ---------------- Orders ----------------
    public function assignedOrders()
    {
        return $this->handleRequest(function () {
            $rider = auth()->user();

            $orders = $this->riderService->assignedOrders($rider->id);
            return response()->json(['success' => true, 'data' => $orders]);
        });
    }

    public function updateOrderStatus(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $data = $request->validate([
                'status' => 'required|string|in:picked,on_the_way,delivered,cancelled'
            ]);

            $order = $this->riderService->updateOrderStatus($id, $data['status']);
            return response()->json(['success' => true, 'data' => $order]);
        }, $request);
    }

    public function orderHistory()
    {
        return $this->handleRequest(function () {
            $rider = auth()->user();
            $history = $this->riderService->orderHistory($rider->id);

            return response()->json(['success' => true, 'data' => $history]);
        });
    }

    // ---------------- Earnings ----------------
    public function earnings()
    {
        return $this->handleRequest(function () {
            $rider = auth()->user();
            $earnings = $this->riderService->earnings($rider->id);

            return response()->json(['success' => true, 'data' => $earnings]);
        });
    }
}
