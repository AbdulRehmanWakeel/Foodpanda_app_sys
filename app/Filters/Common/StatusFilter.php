<?php

namespace App\Filters\Common;

use Illuminate\Database\Eloquent\Builder;

class StatusFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('status', $value);
    }
}