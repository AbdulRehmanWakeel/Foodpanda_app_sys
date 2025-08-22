<?php

namespace App\Services;

use App\Services\Contracts\MenuServiceInterface;
use App\Models\Menu;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MenuService implements MenuServiceInterface
{
    public function createMenu(array $data): mixed
    {
        return Menu::create($data);
    }

    public function updateMenu(int $id, array $data): mixed
    {
        $menu = Menu::find($id);

        if (!$menu) {
            throw new ModelNotFoundException("Menu item not found");
        }

        $menu->update($data);
        return $menu;
    }

    public function deleteMenu(int $id): bool
    {
        $menu = Menu::find($id);

        if (!$menu) {
            throw new ModelNotFoundException("Menu item not found");
        }

        return $menu->delete();
    }
}
