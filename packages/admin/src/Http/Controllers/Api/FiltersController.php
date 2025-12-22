<?php

namespace Admin\Http\Controllers\Api;

use Illuminate\Support\Str;

class FiltersController
{
    /**
     * Handle the incoming request.
     *
     * @param    $resource
     * @return \Illuminate\Http\Response
     */
    public function __invoke($resource)
    {
        $class = 'App\Http\Filters\\'.Str::singular(Str::studly($resource)).'Filters';

        return (new $class)->filters();
    }
}
