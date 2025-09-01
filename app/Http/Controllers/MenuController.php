<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\MenuServiceInterface;
use App\Services\ErrorService;
use App\Models\Restaurant;
use Illuminate\Http\UploadedFile;
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

    private function handleRequest(callable $callback, ?Request $request = null)
    {
        try {
            return $callback();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (Throwable $exception) {
            $this->errorService->log($exception, $request);
            $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            return response()->json(['success' => false, 'error' => $exception->getMessage()], $status);
        }
    }

    // List menu items
    public function index(Request $request, $restaurantId)
    {
        return $this->handleRequest(function () use ($request, $restaurantId) {

            $restaurant = Restaurant::find($restaurantId);
            if (!$restaurant) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'meta' => ['current_page' => 1, 'last_page' => 0, 'per_page' => (int)$request->input('per_page', 10), 'total' => 0]
                ]);
            }

            $filters = $request->only(['category','availability','min_price','max_price','q']);

            $perPage = (int) $request->input('per_page', 10);

            $menuItems = $this->menuService->getMenuItems($restaurantId, $filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $menuItems->items(),
                'meta' => [
                    'current_page' => $menuItems->currentPage(),
                    'last_page' => $menuItems->lastPage(),
                    'per_page' => $menuItems->perPage(),
                    'total' => $menuItems->total(),
                ]
            ]);
        }, $request);
    }

    // Show single menu item
    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $menuItem = $this->menuService->getMenuItemById($id);
            if (!$menuItem) return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
            return response()->json(['success' => true, 'data' => $menuItem]);
        });
    }

    // Create menu item
    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {

            $validated = $request->validate([
                'restaurant_id' => 'required|integer|exists:restaurants,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'category' => 'nullable|string|max:100',
                'availability' => 'nullable|boolean',
                'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:2048',
            ]);

            if ($request->hasFile('image')) $validated['image'] = $request->file('image');

            $menuItem = $this->menuService->createMenuItem($validated);

            return response()->json(['success' => true, 'data' => $menuItem], 201);
        }, $request);
    }

    // Update menu item
    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {

            $menuItem = $this->menuService->getMenuItemById($id);
            if (!$menuItem) return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);

            $validated = $request->validate([
                'restaurant_id' => 'sometimes|integer|exists:restaurants,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|required|numeric',
                'category' => 'nullable|string|max:100',
                'availability' => 'nullable|boolean',
                'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:2048',
            ]);

            if ($request->hasFile('image')) $validated['image'] = $request->file('image');

            $updatedItem = $this->menuService->updateMenuItem($id, $validated);

            return response()->json(['success' => true, 'data' => $updatedItem]);
        }, $request);
    }

    // Delete menu item
    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $deleted = $this->menuService->deleteMenuItem($id);
            if (!$deleted) return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
            return response()->json(['success' => true, 'message' => 'Menu item deleted successfully']);
        });
    }
}
