<?php

namespace App\Filters\Customer;

use Illuminate\Database\Eloquent\Builder;

class PriceRangeFilter
{
    public static function apply(Builder $query, $value)
    {
        $range = explode('-', $value);
        if (count($range) === 2) {
            return $query->whereBetween('avg_price_for_two', [$range[0], $range[1]]);
        }
        return $query->where('avg_price_for_two', '<=', $value);
    }
}