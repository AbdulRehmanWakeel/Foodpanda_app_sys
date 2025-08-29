<?php

namespace App\Filters\Rider;

use Illuminate\Database\Eloquent\Builder;

class DistanceFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('delivery_distance', '<=', $value);
    }
}