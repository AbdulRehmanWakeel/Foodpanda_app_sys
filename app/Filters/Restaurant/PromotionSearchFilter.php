<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PromotionSearchFilter
{
    public static function apply(Builder $query, $value): Builder
    {
        return $query->where(function ($q) use ($value) {
            $q->where('title', 'like', "%{$value}%")
              ->orWhere('description', 'like', "%{$value}%");
        });
    }
}
