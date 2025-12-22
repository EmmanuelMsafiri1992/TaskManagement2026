<?php

namespace Admin\Http\Controllers\Api;

use App\Models\RecentProject;
use Illuminate\Contracts\Database\Eloquent\Builder;

class RecentProjects
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        return RecentProject::whereUserId(auth()->id())
                    ->whereHas('project', function (Builder $query) {
                        $query->whereNull('deleted_at');
                    })
                    ->with('project')
                    ->latest('updated_at')
                    ->take(10)
                    ->get();
    }
}
