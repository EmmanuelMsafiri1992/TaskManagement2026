<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectDuplicate
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Project $project)
    {
        $clone = $project->replicate();
        $clone->save();
        $clone->users()->attach($project->users->pluck('id'));

        return ['success' => true];
    }
}
