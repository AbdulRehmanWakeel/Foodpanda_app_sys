<?php

namespace App\Services;

use App\Services\Contracts\CartServiceInterface;
use App\Models\Cart;
use App\Helpers\FilterPipeline;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartService implements CartServiceInterface
{
    public function getCarts(array $filters = [], int $perPage = 10, $request = null)
    {
        $query = Cart::with('items');

        if ($request) {
            $filterMap = [
                'status'      => \App\Filters\Common\StatusFilter::class,
                'date_range'  => \App\Filters\Common\DateRangeFilter::class,
                'search'      => \App\Filters\Common\SearchFilter::class,
            ];

            $query = FilterPipeline::apply($query, $filters, $filterMap);
        }

        return $query->paginate($perPage);
    }

    public function getCartById(int $id): ?Cart
    {
        return Cart::with('items')->findOrFail($id);
    }

    public function createCart(array $data): Cart
    {
        if (!isset($data['restaurant_id']) || !$data['restaurant_id']) {
            throw new \Exception('Cannot create cart without restaurant_id.');
        }

        if (!isset($data['user_id']) || !$data['user_id']) {
            throw new \Exception('Cannot create cart without user_id.');
        }

        return Cart::create($data);
    }

    public function updateCart(int $id, array $data): ?Cart
    {
        $cart = Cart::findOrFail($id);
        $cart->update($data);
        return $cart;
    }

    public function deleteCart(int $id): bool
    {
        $cart = Cart::findOrFail($id);
        return $cart->delete();
    }
}
