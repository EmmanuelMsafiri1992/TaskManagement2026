<?php

namespace App\Http\Filters;

use AhsanDev\Support\Filters\Filter;

class UserSearch extends Filter
{
    /**
     * The attribute / column name of the field.
     *
     * @var string
     */
    public string $attribute = 'search';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, $value)
    {
        if (empty($value)) {
            return $query;
        }

        return $query->where(function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('email', 'like', "%{$value}%");
        });
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options(): array
    {
        return [];
    }
}
