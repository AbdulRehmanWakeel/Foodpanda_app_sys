<?php

namespace App\Services\Contracts;

use App\Models\Promotion;

interface PromotionServiceInterface
{
    public function getPromotions(array $filters = [], int $perPage = 10, $request = null);
    public function getPromotionById(int $id): ?Promotion;
    public function createPromotion(array $data): Promotion;
    public function updatePromotion(int $id, array $data): ?Promotion;
    public function deletePromotion(int $id): bool;
}
