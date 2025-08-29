<?php
namespace App\Filters\Common;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DateRangeFilter
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