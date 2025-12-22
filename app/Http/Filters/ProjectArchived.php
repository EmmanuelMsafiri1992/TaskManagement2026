<?php

namespace App\Http\Filters;

use AhsanDev\Support\Filters\Filter;

class ProjectArchived extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name = 'Archived';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'filter-select';

    /**
     * The attribute / column name of the field.
     *
     * @var string
     */
    public $attribute = 'archived';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($query, $value)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Get the filter's available options.
     *
     * @return array
     */
    public function options()
    {
        return [
            'Archived' => 1,
        ];
    }
}
