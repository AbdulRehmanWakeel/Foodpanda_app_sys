<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\CartItemServiceInterface;
use App\Services\ErrorService;
use Throwable;

class CartItemController extends Controller
{
    protected $cartItemService;
    protected $errorService;

    public function __construct(CartItemServiceInterface $cartItemService, ErrorService $errorService)
    {
        $this->cartItemService = $cartItemService;
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

    public function index(Request $request, $cartId)
    {
        return $this->handleRequest(function () use ($request, $cartId) {
            $filters = $request->only(['date_range', 'search']);
            $perPage = $request->query('per_page', 10);

            $items = $this->cartItemService->getCartItems($cartId, $filters, $perPage, $request);

            return response()->json([
                'success' => true,
                'data'    => $items->items(),
                'meta'    => [
                    'current_page' => $items->currentPage(),
                    'last_page'    => $items->lastPage(),
                    'per_page'     => $items->perPage(),
                    'total'        => $items->total(),
                ],
            ]);
        }, $request);
    }

    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $item = $this->cartItemService->getCartItemById($id);
            return response()->json(['success' => true, 'data' => $item]);
        });
    }

    public function store(Request $request, $cartId)
    {
        return $this->handleRequest(function () use ($request, $cartId) {
            $validated = $request->validate([
                'menu_id'  => 'required|exists:menus,id',
                'quantity' => 'required|integer|min:1',
                'price'    => 'required|numeric|min:0',
                'addons'   => 'nullable|array',
            ]);

            $item = $this->cartItemService->createCartItem($cartId, $validated);

            return response()->json(['success' => true, 'data' => $item], 201);
        }, $request);
    }

    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'quantity' => 'sometimes|required|integer|min:1',
                'price'    => 'sometimes|required|numeric|min:0',
                'addons'   => 'nullable|array',
            ]);

            $item = $this->cartItemService->updateCartItem($id, $validated);

            return response()->json(['success' => true, 'data' => $item]);
        }, $request);
    }

    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $this->cartItemService->deleteCartItem($id);
            return response()->json(['success' => true, 'message' => 'Cart item deleted successfully']);
        });
    }
}
