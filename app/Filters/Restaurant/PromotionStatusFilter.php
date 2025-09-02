<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PromotionStatusFilter
{
    public static function apply(Builder $query, $value): Builder
    {
        return $query->where('status', $value);
    }
}

