<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Contracts\PromotionServiceInterface;
use App\Services\ErrorService;
use App\Models\Restaurant;
use Throwable;

class PromotionController extends Controller
{
    protected $promotionService;
    protected $errorService;

    public function __construct(PromotionServiceInterface $promotionService, ErrorService $errorService)
    {
        $this->promotionService = $promotionService;
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
            $filters = $request->only(['status', 'date', 'type']);
            $perPage = $request->query('per_page', 10);
            $promotions = $this->promotionService->getPromotions($filters, $perPage, $request);

            return response()->json([
                'success' => true,
                'data'    => $promotions->items(),
                'meta'    => [
                    'current_page' => $promotions->currentPage(),
                    'last_page'    => $promotions->lastPage(),
                    'per_page'     => $promotions->perPage(),
                    'total'        => $promotions->total(),
                ],
            ]);
        }, $request);
    }

    public function show($id)
    {
        return $this->handleRequest(function () use ($id) {
            $promotion = $this->promotionService->getPromotionById($id);

            return response()->json([
                'success' => true,
                'data'    => $promotion,
            ]);
        });
    }

    public function store(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'discount'    => 'required|numeric|min:0',
                'type'        => 'required|string|in:percentage,fixed,free_delivery',
                'status'      => 'required|string|in:active,inactive',
                'start_date'  => 'required|date',
                'end_date'    => 'nullable|date|after_or_equal:start_date',
            ]);

            $user = auth()->user();
            $restaurantId = $user->restaurant_id ?? $user->restaurant?->id;

            if (!$restaurantId) {
                $restaurant = Restaurant::create([
                    'name'    => $user->name . "'s Restaurant",
                    'user_id' => $user->id,
                    'email'   => $user->email ?? 'default@example.com',
                    'phone'   => $user->phone ?? null,
                    'address' => $user->address ?? 'Default Address',
                ]);
                $restaurantId = $restaurant->id;
            }

            $validated['restaurant_id'] = $restaurantId;

            $promotion = $this->promotionService->createPromotion($validated);

            return response()->json([
                'success' => true,
                'data'    => $promotion,
            ], 201);
        }, $request);
    }

    public function update(Request $request, $id)
    {
        return $this->handleRequest(function () use ($request, $id) {
            $user = auth()->user();
            $restaurantId = $user->restaurant_id ?? $user->restaurant?->id;

            if (!$restaurantId) {
                $restaurant = Restaurant::create([
                    'name'    => $user->name . "'s Restaurant",
                    'user_id' => $user->id,
                    'email'   => $user->email ?? 'default@example.com',
                    'phone'   => $user->phone ?? null,
                    'address' => $user->address ?? 'Default Address',
                ]);
                $restaurantId = $restaurant->id;
            }

            $validated = $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'discount'    => 'sometimes|required|numeric|min:0',
                'type'        => 'sometimes|required|string|in:percentage,fixed,free_delivery',
                'status'      => 'sometimes|required|string|in:active,inactive',
                'start_date'  => 'sometimes|required|date',
                'end_date'    => 'nullable|date|after_or_equal:start_date',
            ]);

            $validated['restaurant_id'] = $restaurantId;

            $promotion = $this->promotionService->updatePromotion((int) $id, $validated);

            return response()->json([
                'success' => true,
                'data'    => $promotion,
            ]);
        }, $request);
    }

    public function destroy($id)
    {
        return $this->handleRequest(function () use ($id) {
            $promotion = $this->promotionService->getPromotionById((int) $id);
            $restaurantId = auth()->user()->restaurant_id ?? auth()->user()->restaurant?->id;

            if ($promotion->restaurant_id !== $restaurantId) {
                return response()->json([
                    'success' => false,
                    'error'   => 'You do not have permission to delete this promotion.',
                ], 403);
            }

            $this->promotionService->deletePromotion((int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Promotion deleted successfully',
            ]);
        });
    }
}
