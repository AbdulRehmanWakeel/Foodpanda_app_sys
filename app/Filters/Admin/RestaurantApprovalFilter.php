<?php

namespace App\Filters\Admin;

use Illuminate\Database\Eloquent\Builder;

class RestaurantApprovalFilter
{
    public static function apply(Builder $query, $value)
    {
        // example: 'approved', 'pending', 'rejected'
        return $query->where('approval_status', $value);
    }
}
