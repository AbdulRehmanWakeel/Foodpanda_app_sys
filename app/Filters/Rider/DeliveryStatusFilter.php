<?php

namespace App\Filters\Rider;

use Illuminate\Database\Eloquent\Builder;

class DeliveryStatusFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('delivery_status', $value);
    }
}