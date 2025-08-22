<?php

namespace App\Services\Contracts;

interface MenuServiceInterface
{
    public function createMenu(array $data): mixed;
    public function updateMenu(int $id, array $data): mixed;
    public function deleteMenu(int $id): bool;
}
