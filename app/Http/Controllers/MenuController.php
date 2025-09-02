<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\MenuServiceInterface;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    protected MenuServiceInterface $menuService;

    public function __construct(MenuServiceInterface $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * List all menu items for a restaurant with optional filters
     * GET /restaurant/menu/{restaurantId}
     */
    public function index(Request $request, int $restaurantId): JsonResponse
    {
        $filters = $request->only(['category', 'is_available', 'min_price', 'max_price', 'q']);
        $perPage = $request->get('per_page', 10);

        $menus = $this->menuService->getMenuItems($restaurantId, $filters, $perPage);

        return response()->json([
            'success' => true,
            'data'    => $menus->items(),
            'meta'    => [
                'current_page' => $menus->currentPage(),
                'last_page'    => $menus->lastPage(),
                'per_page'     => $menus->perPage(),
                'total'        => $menus->total(),
            ]
        ]);
    }

    /**
     * Get a single menu item by ID
     * GET /menu/{id}
     */
    public function show(int $id): JsonResponse
    {
        $menu = $this->menuService->getMenuItemById($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $menu
        ]);
    }

    /**
     * Create a new menu item
     * POST /menu
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'description'   => 'nullable|string',
            'category'      => 'nullable|string|max:255',
            'is_available'  => 'nullable|boolean',
            'image'         => 'nullable'
        ]);

        $menu = $this->menuService->createMenuItem($validated);

        return response()->json([
            'success' => true,
            'data'    => $menu
        ], 201);
    }

    /**
     * Update a menu item
     * PUT /menu/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
            'name'          => 'nullable|string|max:255',
            'price'         => 'nullable|numeric|min:0',
            'description'   => 'nullable|string',
            'category'      => 'nullable|string|max:255',
            'is_available'  => 'nullable|boolean',
            'image'         => 'nullable'
        ]);

        $menu = $this->menuService->updateMenuItem($id, $validated);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $menu
        ]);
    }

    /**
     * Delete a menu item
     * DELETE /menu/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->menuService->deleteMenuItem($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully'
        ]);
    }
}
