<?php

namespace App\Filters\Customer;

use Illuminate\Database\Eloquent\Builder;

class DeliveryTimeFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('avg_delivery_time', '<=', $value);
    }
}