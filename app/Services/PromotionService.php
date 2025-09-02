<?php

namespace App\Services;

use App\Services\Contracts\PromotionServiceInterface;
use App\Models\Promotion;
use App\Helpers\FilterPipeline;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PromotionService implements PromotionServiceInterface
{
    public function getPromotions(array $filters = [], int $perPage = 10, $request = null)
    {
        $query = Promotion::query();

        if ($request) {
            $filterMap = [
                'status' => \App\Filters\PromotionStatusFilter::class,
                'date'   => \App\Filters\PromotionDateFilter::class,
                'search' => \App\Filters\PromotionSearchFilter::class,
            ];
            

            $query = FilterPipeline::apply($query, $filters, $filterMap);
        }

        return $query->paginate($perPage);
    }

    public function getPromotionById(int $id): ?Promotion
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            throw new ModelNotFoundException("Promotion not found");
        }

        return $promotion;
    }

    public function createPromotion(array $data): Promotion
    {
        if (!isset($data['restaurant_id']) || !$data['restaurant_id']) {
            throw new \Exception('Cannot create promotion without restaurant_id.');
        }
        return Promotion::create($data);
    }
    public function updatePromotion(int $id, array $data): Promotion
    {
        $promotion = Promotion::findOrFail($id);
        if (isset($data['restaurant_id']) && $promotion->restaurant_id !== $data['restaurant_id']) {
            $promotion->restaurant_id = $data['restaurant_id'];
        }
        $promotion->update($data);
        return $promotion;
    }

    
    public function deletePromotion(int $id): bool
    {
        $promotion = Promotion::find($id);

        if (!$promotion) {
            throw new ModelNotFoundException("Promotion not found");
        }

        return $promotion->delete();
    }
}
