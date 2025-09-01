<?php

namespace App\Filters\Restaurant;

use Illuminate\Database\Eloquent\Builder;

class MenuAvailabilityFilter
{
    public static function apply(Builder $query, $value)
    {
        // Filter by availability (column `is_available`)
        return $query->where('is_available', $value);
    }
}
