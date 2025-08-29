<?php

namespace App\Services\Contracts;

interface MenuServiceInterface
{
    public function getMenuItems(int $restaurantId, array $filters = [], int $perPage = 10);
    public function getMenuItemById(int $id);
    public function createMenuItem(array $data);
    public function updateMenuItem(int $id, array $data);
    public function deleteMenuItem(int $id);
}

