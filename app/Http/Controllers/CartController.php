<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\CartServiceInterface;
use App\Services\ErrorService;
use Throwable;

class CartController extends Controller
{
    protected $cartService;
    protected $errorService;

    public function __construct(CartServiceInterface $cartService, ErrorService $errorService)
    {
        $this->cartService = $cartService;
        $this->errorService = $errorService;
    }

    private function handleRequest(callable $callback, ?Request $request = null)
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            $this->errorService->log($exception, $request);
            $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

            return response()->json([
                'success' => false,
                'error'   => $exception->getMessage(),
            ], $status);
        }
    }

    public function index(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $filters = $request->only(['status', 'date_range', 'search']);
            $perPage = $request->query('per_page', 10);
            $carts = $this->cartService->getCarts($filters, $perPage, $request);

            return response()->json([
                'success' => true,
                'data'    => $carts->items(),
                'meta'    => [
                    'current_page' => $carts->currentPage(),
                    'last_page'    => $carts->lastPage(),
                    'per_page'     => $carts->perPage(),
                    'total'        => $carts->total(),
                ],
            ]);
        }, $request);
    }

    public function show($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'data'    => $this->cartService->getCartById($id),
        ]));
    }

    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'restaurant_id' => 'required|exists:restaurants,id',
            ]);

            $validated['user_id'] = auth()->id();

            $cart = $this->cartService->createCart($validated);

            return response()->json(['success' => true, 'data' => $cart], 201);
        }, $request);
    }

    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'restaurant_id' => 'sometimes|exists:restaurants,id',
            ]);

            $cart = $this->cartService->updateCart((int) $id, $validated);

            return response()->json(['success' => true, 'data' => $cart]);
        }, $request);
    }

    public function destroy($id)
    {
        return $this->handleRequest(fn() => response()->json([
            'success' => true,
            'message' => $this->cartService->deleteCart((int) $id) ? 'Cart deleted successfully' : 'Failed to delete cart',
        ]));
    }
}
