<?php

namespace App\Services;

use App\Services\Contracts\CartItemServiceInterface;
use App\Models\CartItem;
use App\Helpers\FilterPipeline;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartItemService implements CartItemServiceInterface
{
    public function getCartItems(int $cartId, array $filters = [], int $perPage = 10, $request = null)
    {
        $query = CartItem::with('cart')->where('cart_id', $cartId);

        if ($request) {
            $filterMap = [
                'date_range' => \App\Filters\Common\DateRangeFilter::class,
                'search'     => \App\Filters\Common\SearchFilter::class,
            ];

            $query = FilterPipeline::apply($query, $filters, $filterMap);
        }

        return $query->paginate($perPage);
    }

    public function getCartItemById(int $id): ?CartItem
    {
        return CartItem::with('cart')->findOrFail($id);
    }

    public function createCartItem(int $cartId, array $data): CartItem
    {
        $data['cart_id'] = $cartId;

        if (!isset($data['menu_id']) || !$data['menu_id']) {
            throw new \Exception('Cannot create cart item without menu_id.');
        }

        if (!isset($data['quantity']) || !$data['quantity']) {
            $data['quantity'] = 1;
        }

        return CartItem::create($data);
    }

    public function updateCartItem(int $id, array $data): ?CartItem
    {
        $item = CartItem::findOrFail($id);
        $item->update($data);
        return $item;
    }

    public function deleteCartItem(int $id): bool
    {
        $item = CartItem::findOrFail($id);
        return $item->delete();
    }
}
