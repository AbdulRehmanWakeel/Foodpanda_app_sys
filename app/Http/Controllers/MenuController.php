<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Contracts\MenuServiceInterface;

class MenuController extends Controller
{
    protected MenuServiceInterface $menuService;

    public function __construct(MenuServiceInterface $menuService)
    {
        $this->menuService = $menuService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'image' => 'nullable|string'
        ]);

        $menu = $this->menuService->createMenu($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully',
            'data' => $menu
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        try {
            $menu = $this->menuService->updateMenu($id, $request->all());
            return response()->json(['success' => true, 'data' => $menu]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->menuService->deleteMenu($id);
            return response()->json(['success' => true, 'message' => 'Menu deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
