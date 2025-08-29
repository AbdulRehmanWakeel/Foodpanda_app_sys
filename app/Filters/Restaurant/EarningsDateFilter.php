<?php

namespace App\Filters\Restaurant;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class EarningsDateFilter
{
    public static function apply(Builder $query, $value)
    {
        $dates = explode(' to ', $value);
        
        if (count($dates) === 1) {
            return $query->whereDate('created_at', $dates[0]);
        }
        
        return $query->whereBetween('created_at', [
            Carbon::parse($dates[0])->startOfDay(),
            Carbon::parse($dates[1])->endOfDay()
        ]);
    }
}