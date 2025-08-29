<?php

namespace App\Filters\Restaurant;

use Illuminate\Database\Eloquent\Builder;

class OrderStatusFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('status', $value);
    }
}