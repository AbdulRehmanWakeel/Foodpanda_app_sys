<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\MenuServiceInterface;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuServiceInterface $menuService)
    {
        $this->menuService = $menuService;
    }

    // List menu items with filters
    public function index(Request $request, $restaurantId)
    {
        $filters = $request->all();
        $perPage = $request->input('per_page', 10);

        $menuItems = $this->menuService->getMenuItems($restaurantId, $filters, $perPage);

        return response()->json($menuItems);
    }

    // Show single menu item
    public function show($id)
    {
        $menuItem = $this->menuService->getMenuItemById($id);
        if (!$menuItem) {
            return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
        }
        return response()->json($menuItem);
    }

    // Create menu item
    public function store(Request $request)
    {
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
    }

    // Update menu item
    public function update(Request $request, $id)
    {
        $menuItem = $this->menuService->updateMenuItem($id, $request->all());

        if (!$menuItem) {
            return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $menuItem]);
    }

    // Delete menu item
    public function destroy($id)
    {
        $deleted = $this->menuService->deleteMenuItem($id);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Menu item deleted successfully']);
    }
}
