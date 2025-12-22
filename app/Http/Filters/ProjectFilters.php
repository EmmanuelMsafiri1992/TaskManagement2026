<?php

namespace App\Http\Filters;

use AhsanDev\Support\Filters\Filters;

class ProjectFilters extends Filters
{
    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            new ProjectArchived,
        ];
    }
}
