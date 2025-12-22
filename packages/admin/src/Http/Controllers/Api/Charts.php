<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Charts
{
    public function __invoke(): array
    {
        return [
            'chart_tasks_yearly' => $this->chartTasksYearly(),
            'chart_tasks_weekly' => $this->chartTasksWeekly(),
        ];
    }

    protected function getTodayEndDate()
    {
        return today()->add('23 Hours 59 Minutes 59 Seconds');

        return today()->add('18 Hours 59 Minutes 59 Seconds');
    }

    protected function chartTasksWeekly()
    {
        $query = DB::table('tasks');

        $query->select(DB::raw("date_format(tasks.completed_at + INTERVAL 0 HOUR, '%Y-%m-%d') as date_result, count(tasks.id) as aggregate"))
                ->whereBetween('tasks.completed_at', [today()->addDay()->subWeek(), $this->getTodayEndDate()])
                ->whereNull('parent_id')
                ->whereExists(function ($q) {
                    $q->select(DB::raw('*'))
                        ->from('users')
                        ->join('task_user', 'users.id', '=', 'task_user.user_id')
                        ->whereColumn('task_user.task_id', 'tasks.id')
                        ->whereId(auth()->id());
                })
                ->groupBy(DB::raw("date_format(tasks.completed_at + INTERVAL 0 HOUR, '%Y-%m-%d')"))
                ->orderBy('date_result');

        $results = $query->get();

        $array = $results->map(function ($item) {
            return [Carbon::parse($item->date_result)->format('D') => $item->aggregate];
        });

        $data = $array->collapse();

        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $day = today()->addDay($i + 1)->format('D');
            $days[$day] = $data[$day] ?? 0;
        }

        return $days;
    }

    protected function chartTasksYearly()
    {
        $query = DB::table('tasks');

        $query->select(DB::raw("date_format(tasks.completed_at + INTERVAL 0 HOUR, '%Y-%m') as date_result, count(tasks.id) as aggregate"))
                ->whereBetween('tasks.completed_at', [today()->startOfYear(), today()->endOfYear()])
                ->whereNull('parent_id')
                ->whereExists(function ($q) {
                    $q->select(DB::raw('*'))
                        ->from('users')
                        ->join('task_user', 'users.id', '=', 'task_user.user_id')
                        ->whereColumn('task_user.task_id', 'tasks.id')
                        ->whereId(auth()->id());
                })
                ->groupBy(DB::raw("date_format(tasks.completed_at + INTERVAL 0 HOUR, '%Y-%m')"))
                ->orderBy('date_result');

        $results = $query->get();

        $array = $results->map(function ($item) {
            return [Carbon::parse($item->date_result)->format('M') => $item->aggregate];
        });

        $data = $array->collapse();

        $months = [];

        for ($i = 0; $i < 12; $i++) {
            $day = today()->month($i + 1)->format('M');
            $months[$day] = $data[$day] ?? 0;
        }

        return $months;
    }
}
