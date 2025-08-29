<?php

namespace App\Filters\Customer;

use Illuminate\Database\Eloquent\Builder;

class RatingFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('rating', '>=', $value);
    }
}