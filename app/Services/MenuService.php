<?php

namespace App\Services;

use App\Models\Menu;
use App\Helpers\FilterPipeline;
use App\Services\Contracts\MenuServiceInterface;
use Illuminate\Http\UploadedFile;

class MenuService implements MenuServiceInterface
{
    // Get paginated menu items
    public function getMenuItems(int $restaurantId, array $filters = [], int $perPage = 10)
    {
        $query = Menu::where('restaurant_id', $restaurantId)->with('restaurant');

        $filterMap = [
            'category' => \App\Filters\MenuCategoryFilter::class,
            'availability' => \App\Filters\MenuAvailabilityFilter::class,
            'min_price' => \App\Filters\MenuMinPriceFilter::class,
            'max_price' => \App\Filters\MenuMaxPriceFilter::class,
            'q' => \App\Filters\MenuSearchFilter::class,
        ];

        // Keep only valid filters with non-empty values
        $filters = array_filter($filters, fn($key, $value) => isset($filterMap[$key]) && $value !== null, ARRAY_FILTER_USE_BOTH);

        // Cast availability to integer for DB match
        if (isset($filters['availability'])) {
            $filters['availability'] = filter_var($filters['availability'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }

        $query = FilterPipeline::apply($query, $filters, $filterMap);

        $paginated = $query->paginate($perPage);

        // Format image for frontend
        $paginated->getCollection()->transform(function ($item) {
            if ($item->image) {
                $item->image = [
                    'image_path' => $item->image,
                    'url' => url('storage/' . $item->image),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            }
            return $item;
        });

        return $paginated;
    }

    // Get single menu item
    public function getMenuItemById(int $id)
    {
        $menu = Menu::with('restaurant')->find($id);
        if ($menu && $menu->image) {
            $menu->image = [
                'image_path' => $menu->image,
                'url' => url('storage/' . $menu->image),
                'created_at' => $menu->created_at,
                'updated_at' => $menu->updated_at,
            ];
        }
        return $menu;
    }

    // Create menu item
    public function createMenuItem(array $data)
    {
        $data['availability'] = $data['availability'] ?? true;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('menus', 'public');
        }

        return Menu::create([
            'restaurant_id' => $data['restaurant_id'],
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'is_available' => $data['availability'],
            'image' => $data['image'] ?? null,
        ]);
    }

    // Update menu item
    public function updateMenuItem(int $id, array $data)
    {
        $menu = Menu::find($id);
        if (!$menu) return null;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('menus', 'public');
        }

        $menu->update([
            'restaurant_id' => $data['restaurant_id'] ?? $menu->restaurant_id,
            'name' => $data['name'] ?? $menu->name,
            'price' => $data['price'] ?? $menu->price,
            'description' => $data['description'] ?? $menu->description,
            'category' => $data['category'] ?? $menu->category,
            'is_available' => $data['availability'] ?? $menu->is_available,
            'image' => $data['image'] ?? $menu->image,
        ]);

        return $menu;
    }

    // Delete menu item
    public function deleteMenuItem(int $id)
    {
        $menu = Menu::find($id);
        if (!$menu) return false;
        return $menu->delete();
    }
}
