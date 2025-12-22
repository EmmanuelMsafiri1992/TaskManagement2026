<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ProjectsOptions
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return Project::query()
            ->whereHas('users', function (Builder $q) {
                $q->whereId(auth()->id());
            })
            ->get();
    }
}
