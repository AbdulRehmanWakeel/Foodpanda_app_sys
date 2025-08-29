<?php

namespace App\Filters\Admin;

use Illuminate\Database\Eloquent\Builder;

class UserStatusFilter
{
    public static function apply(Builder $query, $value)
    {
         
        if ($value === 'inactive') {
             
            return $query->onlyTrashed();
        }

        return $query->where('status', $value);
    }
}
