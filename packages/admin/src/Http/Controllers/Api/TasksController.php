<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Label;
use App\Models\Priority;
use App\Models\ProjectList;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TasksController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Task::query();

        $query
            ->whereHas('users', function (Builder $q) {
                $q->where('id', auth()->id());
            })
            ->with('project', 'users')
            ->whereNull('parent_id');

        if (request('type') == 'today') {
            return ['today' => $query->whereNull('completed_at')->whereDate('due_at', today())->get()];
        } elseif (request('type') == 'upcoming') {
            return ['upcoming' => $query->whereNull('completed_at')->whereDate('due_at', '>', today())->get()];
        } elseif (request('type') == 'overdue') {
            return ['overdue' => $query->whereNull('completed_at')->whereDate('due_at', '<', today())->get()];
        } elseif (request('type') == 'completed') {
            return ['completed' => $query->whereNotNull('completed_at')->get()];
        }

        if (request('project')) {
            $query->where('project_id', request('project'));
        }

        return $query->whereNull('completed_at')->get()
            ->groupBy(function ($item) {
                if (! $item->due_at) {
                    return 'no overdue';
                }

                if ($item->due_at->isToday()) {
                    return 'today';
                }

                if ($item->due_at->isFuture()) {
                    return 'upcoming';
                }

                if ($item->due_at->isPast()) {
                    return 'overdue';
                }
            });
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $prevOrder = optional(Task::whereProjectId($request->project_id)->whereProjectListId($request->list_id)->latest('id')->first())->order;
        $prevOrder = $prevOrder === null ? -1 : $prevOrder;

        $task = Task::create([
            'title' => $request->task,
            'project_id' => $request->project_id,
            'project_list_id' => $request->list_id,
            'order' => $prevOrder + 1,
        ]);

        // Add "not started" label to new tasks by default
        $notStartedLabel = \App\Models\Label::where('name', 'not started')->first();
        if ($notStartedLabel) {
            $task->labels()->attach($notStartedLabel->id);
        }

        return $task->load('tasks', 'comments', 'users', 'checklists', 'labels');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return [
            'options' => [
                'priority' => Priority::get(['id', 'name', 'color']),
                'project_lists' => ProjectList::whereProjectId($task->project_id)->get(['id', 'name']),
                'labels' => Label::get(),
            ],
            'task' => $task->load(
                'project',
                'projectList',
                'project.users',
                'priority',
                'users',
                'labels',
                'checklists.checklistItems',
                'comments.user',
                'comments.attachments',
                'attachments',
                'timelogs.user',
            ),
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $task->update($request->only('title', 'description'));

        return ['success' => true];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function complete(Task $task)
    {
        if ($task->completed_at) {
            $task->update(['completed_at' => null]);

            return ['success' => true, 'completed_at' => null];
        }

        $task->update([
            'completed_at' => now(),
        ]);

        return ['success' => true, 'completed_at' => 'true'];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function start(Task $task)
    {
        $task->update([
            'start_at' => request('start_at'),
        ]);

        return ['success' => true];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function due(Task $task)
    {
        $task->update([
            'due_at' => request('due_at'),
        ]);

        return ['success' => true];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function task(Task $task)
    {
        $task->update(['task' => request('task')]);

        return ['success' => true];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function content(Task $task)
    {
        $task->update([
            'title' => request('title'),
            'description' => request('description'),
        ]);

        return ['success' => true];
    }

    /**
     * Display a listing of the resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::withTrashed()->find($id);

        $task->users()->detach();
        $task->timelogs()->delete();
        $task->tasks()->forceDelete();
        $task->comments()->forceDelete();
        $task->attachments()->forceDelete();
        $task->forceDelete();

        return [
            'message' => 'Task Deleted Successfully!',
        ];
    }
}
