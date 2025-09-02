<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PromotionDateFilter
{
    public static function apply(Builder $query, $value): Builder
    {
        // value should be ['from' => '2025-09-01', 'to' => '2025-09-30']
        if (isset($value['from'])) {
            $query->whereDate('start_date', '>=', $value['from']);
        }
        if (isset($value['to'])) {
            $query->whereDate('end_date', '<=', $value['to']);
        }
        return $query;
    }
}
