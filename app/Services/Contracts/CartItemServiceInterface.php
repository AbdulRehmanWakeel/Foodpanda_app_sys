<?php

namespace App\Services\Contracts;

use App\Models\CartItem;

interface CartItemServiceInterface
{
    public function getCartItems(int $cartId, array $filters = [], int $perPage = 10, $request = null);
    public function getCartItemById(int $id): ?CartItem;
    public function createCartItem(int $cartId, array $data): CartItem;
    public function updateCartItem(int $id, array $data): ?CartItem;
    public function deleteCartItem(int $id): bool;
}
