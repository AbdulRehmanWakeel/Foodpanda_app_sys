<?php

namespace App\Filters\Restaurant;

use Illuminate\Database\Eloquent\Builder;

class MenuCategoryFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('category_id', $value);
    }
}