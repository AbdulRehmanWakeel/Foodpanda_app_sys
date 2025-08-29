<?php

namespace App\Services;

use App\Models\Menu;
use App\Helpers\FilterPipeline;
use App\Services\Contracts\MenuServiceInterface;

class MenuService implements MenuServiceInterface
{
    public function getMenuItems(int $restaurantId, array $filters = [], int $perPage = 10)
    {
        $query = Menu::where('restaurant_id', $restaurantId);

        // Define filter map
        $filterMap = [
            'category' => \App\Filters\MenuCategoryFilter::class,
            'availability' => \App\Filters\MenuAvailabilityFilter::class,
            'min_price' => \App\Filters\MenuMinPriceFilter::class,
            'max_price' => \App\Filters\MenuMaxPriceFilter::class,
            'q' => \App\Filters\MenuSearchFilter::class,  
        ];

        $query = FilterPipeline::apply($query, $filters, $filterMap);

        return $query->paginate($perPage);
    }

    public function getMenuItemById(int $id)
    {
        return Menu::find($id);
    }

    public function createMenuItem(array $data)
    {
        return Menu::create($data);
    }

    public function updateMenuItem(int $id, array $data)
    {
        $menu = Menu::find($id);
        if (!$menu) return null;

        $menu->update($data);
        return $menu;
    }

    public function deleteMenuItem(int $id)
    {
        $menu = Menu::find($id);
        if (!$menu) return false;

        return $menu->delete();
    }
}
