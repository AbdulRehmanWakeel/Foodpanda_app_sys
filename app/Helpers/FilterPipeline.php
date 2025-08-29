<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;

class FilterPipeline
{
    /**
     * Apply filters to a query using a filter map.
     *
     * @param  Builder|\Illuminate\Database\Eloquent\Relation  $query
     * @param  array  $filters   // typically request->all()
     * @param  array  $filterMap // ['qParam' => FilterClass::class, ...]
     * @return Builder
     */
    public static function apply($query, array $filters, array $filterMap)
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (isset($filterMap[$key]) && class_exists($filterMap[$key])) {
                $filterClass = $filterMap[$key];

                 
                if (method_exists($filterClass, 'apply')) {
                    $query = $filterClass::apply($query, $value);
                } else {
                    $filter = new $filterClass();
                    if (method_exists($filter, 'handle')) {
                        $query = $filter->handle($query, $value);
                    }
                }
            }
        }

        return $query;
    }
}
