<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\MenuServiceInterface;
use App\Services\ErrorService;
use Throwable;

class MenuController extends Controller
{
    protected $menuService;
    protected $errorService;

    public function __construct(MenuServiceInterface $menuService, ErrorService $errorService)
    {
        $this->menuService = $menuService;
        $this->errorService = $errorService;
    }

    /**
     * Generic request handler to log errors
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
                'error' => $exception->getMessage()
            ], $status);
        }
    }

    // List menu items with filters
    public function index(Request $request, $restaurantId)
    {
        return $this->handleRequest(function () use ($request, $restaurantId) {
            $filters = $request->all();
            $perPage = $request->input('per_page', 10);
            $menuItems = $this->menuService->getMenuItems($restaurantId, $filters, $perPage);
            return response()->json(['success' => true, 'data' => $menuItems]);
        }, $request);
    }

    // Show single menu item
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $menuItem = $this->menuService->getMenuItemById($id);
            if (!$menuItem) {
                return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
            }
            return response()->json(['success' => true, 'data' => $menuItem]);
        });
    }

    // Create menu item
    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'restaurant_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'category' => 'nullable|string|max:100',
                'availability' => 'nullable|boolean'
            ]);

            $menuItem = $this->menuService->createMenuItem($request->all());
            return response()->json(['success' => true, 'data' => $menuItem], 201);
        }, $request);
    }

    // Update menu item
    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $menuItem = $this->menuService->updateMenuItem($id, $request->all());

            if (!$menuItem) {
                return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $menuItem]);
        }, $request);
    }

    // Delete menu item
    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $deleted = $this->menuService->deleteMenuItem($id);

            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
            }

            return response()->json(['success' => true, 'message' => 'Menu item deleted successfully']);
        });
    }
}
