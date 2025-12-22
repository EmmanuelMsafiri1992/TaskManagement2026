<?php

namespace Admin\Http\Controllers\Api;

use App\Models\FavoriteProject;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectArchive
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Project $project)
    {
        // if ($project->archived_at) {
        //     $project->update(['archived_at' => null]);

        //     return ['success' => true, 'message' => 'Project restore successfully!'];
        // }

        $currentProject = $project;
        FavoriteProject::whereProjectId($project->id)->delete();
        $project->delete();

        return [
            'success' => true,
            'message' => 'Project archived successfully!',
            'project' => $currentProject,
        ];
    }
}
