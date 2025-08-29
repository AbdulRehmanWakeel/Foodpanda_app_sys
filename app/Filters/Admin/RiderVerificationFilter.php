<?php

namespace App\Filters\Admin;

use Illuminate\Database\Eloquent\Builder;

class RiderVerificationFilter
{
    public static function apply(Builder $query, $value)
    {
        // example values 'verified', 'pending', 'rejected'
        return $query->where('verification_status', $value);
    }
}
