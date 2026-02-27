<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Filters\ProjectFilters;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Models\ProjectList;
use App\Models\RecentProject;
use App\Models\Status;
use App\Models\User;
use AhsanDev\Support\Authorization\Http\Controllers\AuthorizeController;
use AhsanDev\Support\Field;
use Facades\Admin\Static\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProjectsController extends AuthorizeController
{
    protected $name = 'project';

    protected $excludeResource = ['view', 'detail'];

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Filters\ProjectFilters  $filters
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectFilters $filters)
    {
        $query = Project::query();

        if (! auth()->user()->isSuperAdmin()) {
            $query->whereHas('users', function (Builder $q) {
                $q->whereId(auth()->id());
            });
        }

        if (request()->has('archived')) {
            $query->onlyTrashed();
        }

        return $query->get(['id', 'name', 'meta'])
                    ->filter(function ($item) {
                        return $item->append(['is_favorite']);
                    });

        $query->filter($filters)
            ->select('id', 'name', 'color_id')
            ->with('color:id,name', 'users:id,name,avatar')
            ->withCount(['tasks' => function ($q) {
                $q->whereNull('completed_at')->whereNull('parent_id');
            }]);

        return $query->simplePaginate()
            ->through(function ($item) {
                return $item->append(['remaining_users', 'is_favorite']);
            });
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function create(Project $project)
    {
        return $this->fields($project);
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
        return new ProjectRequest($request, $project);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $query = Project::whereId($id);

        if (! auth()->user()->isSuperAdmin()) {
            $query->whereHas('users', function (Builder $q) {
                $q->where('id', auth()->id());
            });
        }

        $project = $query->first();

        if (! $project) {
            abort(403);
        }

        RecentProject::updateOrCreate(
            ['user_id' => auth()->id(), 'project_id' => $project->id],
            ['updated_at' => now()]
        );

        return $project->append(['is_favorite'])->load(['users', 'lists' => function ($query) {
            $query->orderBy('order')->with(['tasks' => function ($q) {
                $q->whereNull('completed_at')->orderBy('order');
            }, 'tasks.users', 'tasks.comments', 'tasks.checklists.checklistItems', 'tasks.priority', 'tasks.labels']);
        }]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        return $this->fields($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        return new ProjectRequest($request, $project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $project = Project::withTrashed()->find($request->items[0]);

        $project->users()->detach();
        // $project->favoriteBy()->detach();
        $project->tasks()->each(fn ($item) => $item->users()->detach());
        $project->tasks()->each(fn ($item) => $item->tasks()->forceDelete());
        $project->tasks()->each(fn ($item) => $item->comments()->forceDelete());
        $project->tasks()->each(fn ($item) => $item->attachments()->forceDelete());

        $project->tasks()->forceDelete();
        $project->lists()->forceDelete();
        $project->forceDelete();

        return [
            'message' => 'Project Deleted Successfully!',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\Project  $project
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function complete(Project $project, Request $request)
    {
        if ($project->completed_at) {
            $project->update(['status_id' => Status::getId('Ongoing'), 'completed_at' => null]);

            return ['success' => true, 'message' => 'Project mark as incomplete successfully!'];
        }

        $project->update(['status_id' => Status::getId('Completed'), 'completed_at' => now()]);

        return ['success' => true, 'message' => 'Project mark as completed successfully!'];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listUpdate(Request $request)
    {
        ProjectList::find($request->list_id)->update([
            'name' => $request->name,
        ]);

        return ['success' => true];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listDelete(Request $request)
    {
        $list = ProjectList::find($request->list_id);
        $list->tasks()->each(fn ($item) => $item->users()->detach());
        $list->tasks()->each(fn ($item) => $item->tasks()->forceDelete());
        $list->tasks()->each(fn ($item) => $item->comments()->forceDelete());
        $list->tasks()->each(fn ($item) => $item->attachments()->forceDelete());
        $list->tasks()->forceDelete();
        $list->forceDelete();

        return ['success' => true];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fields($model)
    {
        return Field::make()
                ->field('name', $model->name)
                ->field('description', $model->description)
                ->field('color', $model->meta['color'] ?? Color::default(), Color::options())
                ->field('users', $model->users->isEmpty() ? [auth()->id()] : $model->users->pluck('id'), User::orderBy('name')->get(['id', 'name', 'email', 'avatar', 'meta']));
    }
}
