<?php
namespace App\Filters\Restaurant;

use Illuminate\Database\Eloquent\Builder;

class MenuMaxPriceFilter
{
    public static function apply(Builder $query, $value)
    {
        return $query->where('price', '<=', $value);
    }
}
