<?php
namespace App\Filters\Customer;
use Illuminate\Database\Eloquent\Builder;

class LocationFilter
{
    public static function apply(Builder $query, $value)
    {
         
        return $query->where('location', 'like', "%{$value}%");
    }
}
