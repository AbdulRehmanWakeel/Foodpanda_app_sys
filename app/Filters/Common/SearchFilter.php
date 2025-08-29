<?php

namespace App\Filters\Common;

use Illuminate\Database\Eloquent\Builder;

class SearchFilter
{
    public static function apply(Builder $query, $value)
    {
        $searchableFields = $query->getModel()->searchableFields ?? ['name'];
        
        return $query->where(function ($q) use ($value, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$value}%");
            }
        });
    }
}