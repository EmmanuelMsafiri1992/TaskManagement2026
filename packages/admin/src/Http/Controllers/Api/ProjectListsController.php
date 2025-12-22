<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Project;
use App\Models\ProjectList;
use Illuminate\Http\Request;

class ProjectListsController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Project $project)
    {
        $prevOrder = ProjectList::whereProjectId($project->id)->latest('id')->first()?->order;
        $prevOrder = $prevOrder === null ? -1 : $prevOrder;

        $projectList = ProjectList::create([
            'name' => $request->name,
            'project_id' => $project->id,
            'order' => $prevOrder + 1,
        ]);

        return $projectList->load('tasks');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProjectList  $list
     * @return \Illuminate\Http\Response
     */
    public function show(ProjectList $list)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProjectList  $list
     * @return \Illuminate\Http\Response
     */
    public function edit(ProjectList $list)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProjectList  $list
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProjectList $list)
    {
        $list->update([
            'name' => $request->name,
        ]);

        return ['success' => true];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProjectList  $list
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProjectList $list)
    {
        $list->tasks()->each(fn ($item) => $item->users()->detach());
        $list->tasks()->each(fn ($item) => $item->tasks()->forceDelete());
        $list->tasks()->each(fn ($item) => $item->comments()->forceDelete());
        $list->tasks()->each(fn ($item) => $item->attachments()->forceDelete());
        $list->tasks()->forceDelete();
        $list->forceDelete();

        return ['success' => true];
    }
}
