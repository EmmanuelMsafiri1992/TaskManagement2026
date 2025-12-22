<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Metrics
{
    public function __invoke(): array
    {
        if (Auth::user()->isSuperAdmin()) {
            return [
                'open_tasks' => Task::whereNull('parent_id')->whereNull('completed_at')->count(),
                'completed_tasks' => Task::whereNull('parent_id')->whereNotNull('completed_at')->count(),
                'total_projects' => Project::count(),
            ];
        }

        return [
            'open_tasks' => Task::whereHas('users', function (Builder $query) {
                $query->where('id', auth()->id());
            })->whereNull('parent_id')->whereNull('completed_at')->count(),

            'completed_tasks' => Task::whereHas('users', function (Builder $query) {
                $query->where('id', auth()->id());
            })->whereNull('parent_id')->whereNotNull('completed_at')->count(),

            'total_projects' => Project::whereHas('users', function (Builder $query) {
                $query->where('id', auth()->id());
            })->count(),
        ];
    }
}
