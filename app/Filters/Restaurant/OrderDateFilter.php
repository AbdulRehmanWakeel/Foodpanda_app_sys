<?php

namespace App\Filters\Restaurant;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OrderDateFilter
{
    public static function apply(Builder $query, $value)
    {
        if ($value === 'today') {
            return $query->whereDate('created_at', Carbon::today());
        } elseif ($value === 'week') {
            return $query->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        } elseif ($value === 'month') {
            return $query->whereMonth('created_at', Carbon::now()->month);
        }
        return $query;
    }
}