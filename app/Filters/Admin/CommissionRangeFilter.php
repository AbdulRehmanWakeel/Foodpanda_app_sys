<?php

namespace App\Filters\Admin;

use Illuminate\Database\Eloquent\Builder;

class CommissionRangeFilter
{
    public static function apply(Builder $query, $value)
    {
        // Accept "min-max" or a single minimum value
        $range = is_string($value) ? explode('-', $value) : (array)$value;
        if (count($range) === 2) {
            return $query->whereBetween('commission_rate', [(float)$range[0], (float)$range[1]]);
        }
        return $query->where('commission_rate', '>=', (float)$range[0]);
    }
}
