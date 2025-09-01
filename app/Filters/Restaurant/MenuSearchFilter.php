<?php

namespace App\Filters\Restaurant;

use Illuminate\Database\Eloquent\Builder;

class MenuSearchFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('name', 'like', "%{$value}%");
    }
}
