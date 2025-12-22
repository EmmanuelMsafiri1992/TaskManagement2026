<?php

namespace App\Http\Filters;

use AhsanDev\Support\Filters\Filters;

class UserFilters extends Filters
{
    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            new UserSearch,
            new UserRole,
        ];
    }
}
