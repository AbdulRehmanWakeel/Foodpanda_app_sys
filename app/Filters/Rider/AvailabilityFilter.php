<?php

namespace App\Filters\Rider;

use Illuminate\Database\Eloquent\Builder;

class AvailabilityFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('availability_status', $value);
    }
}