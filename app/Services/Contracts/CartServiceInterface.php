<?php

namespace App\Services\Contracts;

use App\Models\Cart;

interface CartServiceInterface
{
    public function getCarts(array $filters = [], int $perPage = 10, $request = null);
    public function getCartById(int $id): ?Cart;
    public function createCart(array $data): Cart;
    public function updateCart(int $id, array $data): ?Cart;
    public function deleteCart(int $id): bool;
}

