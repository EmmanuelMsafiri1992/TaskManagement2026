<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Project;

class ProjectRestore
{
    /**
     * Handle the incoming request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        Project::withTrashed()->whereId($id)->restore();

        return Project::find($id);
    }
}
