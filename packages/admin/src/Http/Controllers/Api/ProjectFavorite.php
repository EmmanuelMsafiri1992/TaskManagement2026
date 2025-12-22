<?php

namespace Admin\Http\Controllers\Api;

use App\Models\FavoriteProject;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectFavorite
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Project $project)
    {
        if ($project->isFavorite) {
            FavoriteProject::whereProjectId($project->id)->whereUserId(auth()->id())->delete();

            return [
                'success' => true,
                'favorite' => false,
                'message' => 'Project remove from favorite successfully!',
                'favorites' => Auth::user()->load('favorites.project'),
            ];
        }

        FavoriteProject::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
        ]);

        return [
            'success' => true,
            'favorite' => true,
            'message' => 'Project add to favorite successfully!',
            'favorites' => Auth::user()->load('favorites.project'),
        ];
    }
}
