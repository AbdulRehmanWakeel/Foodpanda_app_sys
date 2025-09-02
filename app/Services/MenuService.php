<?php

namespace App\Services;

use App\Models\Menu;
use App\Helpers\FilterPipeline;
use App\Services\Contracts\MenuServiceInterface;
use Illuminate\Http\UploadedFile;

class MenuService implements MenuServiceInterface
{
    /**
     * List menu items with optional filters and pagination
     */
    public function getMenuItems(int $restaurantId, array $filters = [], int $perPage = 10)
    {
        $query = Menu::where('restaurant_id', $restaurantId);

        $filterMap = [
            'category'     => \App\Filters\MenuCategoryFilter::class,
            'is_available' => \App\Filters\MenuAvailabilityFilter::class,
            'min_price'    => \App\Filters\MenuMinPriceFilter::class,
            'max_price'    => \App\Filters\MenuMaxPriceFilter::class,
            'q'            => \App\Filters\MenuSearchFilter::class,
        ];

        // Keep only valid filters with non-null values
        $filters = array_filter(
            $filters,
            fn($value, $key) => isset($filterMap[$key]) && $value !== null,
            ARRAY_FILTER_USE_BOTH
        );

        // Apply filters using the pipeline
        $query = FilterPipeline::apply($query, $filters, $filterMap);

        // Paginate results
        $paginated = $query->paginate($perPage);

        // Transform image URLs
        $paginated->getCollection()->transform(function ($item) {
            $item->image = $item->image
                ? (filter_var($item->image, FILTER_VALIDATE_URL)
                    ? ['url' => $item->image]
                    : ['url' => url('storage/' . $item->image)])
                : null;
            return $item;
        });

        return $paginated;
    }

    /**
     * Get a single menu item by ID
     */
    public function getMenuItemById(int $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return null;
        }

        $menu->image = $menu->image
            ? (filter_var($menu->image, FILTER_VALIDATE_URL)
                ? ['url' => $menu->image]
                : ['url' => url('storage/' . $menu->image)])
            : null;

        return $menu;
    }

    /**
     * Create a new menu item
     */
    public function createMenuItem(array $data)
    {
        // If image is uploaded file
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('menus', 'public');
        }
        // If image is a URL string, just save it as-is
        if (isset($data['image']) && is_string($data['image']) && filter_var($data['image'], FILTER_VALIDATE_URL)) {
            // keep $data['image'] as URL
        }
        return Menu::create([
            'restaurant_id' => $data['restaurant_id'],
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'is_available' => $data['is_available'] ?? true,
            'image' => $data['image'] ?? null,
        ]);
    }


    /**
     * Update an existing menu item
     */
    public function updateMenuItem(int $id, array $data)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return null;
        }

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('menus', 'public');
        }

        $menu->update([
            'restaurant_id' => $data['restaurant_id'] ?? $menu->restaurant_id,
            'name'          => $data['name'] ?? $menu->name,
            'price'         => $data['price'] ?? $menu->price,
            'description'   => $data['description'] ?? $menu->description,
            'category'      => $data['category'] ?? $menu->category,
            'is_available'  => $data['is_available'] ?? $menu->is_available,
            'image'         => $data['image'] ?? $menu->image,
        ]);

        return $menu;
    }

    /**
     * Delete a menu item by ID
     */
    public function deleteMenuItem(int $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return false;
        }

        return $menu->delete();
    }
}
