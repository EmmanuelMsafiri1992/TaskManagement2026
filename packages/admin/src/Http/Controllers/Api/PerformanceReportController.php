<?php

namespace Admin\Http\Controllers\Api;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceReportController
{
    /**
     * Get performance report for all users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Only allow super admins
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $period = $request->get('period', 'week');
        $today = Carbon::today()->toDateString();
        $startOfWeek = Carbon::now()->startOfWeek()->toDateTimeString();

        // Determine start date based on period
        switch ($period) {
            case 'today':
                $startDate = Carbon::today()->toDateTimeString();
                break;
            case 'week':
                $startDate = $startOfWeek;
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear()->toDateTimeString();
                break;
            default:
                $startDate = null; // All time
        }

        // Single optimized query to get all user stats
        $query = DB::table('users')
            ->select([
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.avatar',
                DB::raw('COUNT(DISTINCT tasks.id) as total_tasks'),
                DB::raw('COUNT(DISTINCT CASE WHEN tasks.completed_at IS NOT NULL THEN tasks.id END) as completed'),
                DB::raw('COUNT(DISTINCT CASE WHEN tasks.completed_at IS NULL THEN tasks.id END) as pending'),
                DB::raw("COUNT(DISTINCT CASE WHEN tasks.completed_at IS NULL AND tasks.due_at IS NOT NULL AND DATE(tasks.due_at) < '{$today}' THEN tasks.id END) as overdue"),
                DB::raw("COUNT(DISTINCT CASE WHEN tasks.completed_at IS NOT NULL AND tasks.due_at IS NOT NULL AND DATE(tasks.completed_at) <= DATE(tasks.due_at) THEN tasks.id END) as on_time"),
                DB::raw("COUNT(DISTINCT CASE WHEN tasks.completed_at IS NOT NULL AND tasks.due_at IS NOT NULL AND DATE(tasks.completed_at) > DATE(tasks.due_at) THEN tasks.id END) as completed_late"),
                DB::raw("COUNT(DISTINCT CASE WHEN DATE(tasks.completed_at) = '{$today}' THEN tasks.id END) as completed_today"),
                DB::raw("COUNT(DISTINCT CASE WHEN tasks.completed_at >= '{$startOfWeek}' THEN tasks.id END) as completed_this_week"),
            ])
            ->leftJoin('task_user', 'users.id', '=', 'task_user.user_id')
            ->leftJoin('tasks', function ($join) use ($startDate) {
                $join->on('task_user.task_id', '=', 'tasks.id')
                    ->whereNull('tasks.deleted_at');
                if ($startDate) {
                    $join->where('tasks.created_at', '>=', $startDate);
                }
            })
            ->whereNotNull('users.email_verified_at')
            ->whereNull('users.deleted_at')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar');

        $results = $query->get();

        $report = [];
        foreach ($results as $row) {
            $totalTasks = (int) $row->total_tasks;
            $completed = (int) $row->completed;
            $onTime = (int) $row->on_time;
            $completedLate = (int) $row->completed_late;

            // Performance percentage
            $performance = $totalTasks > 0 ? round(($completed / $totalTasks) * 100) : 0;

            // On-time rate (of completed tasks with due dates)
            $completedWithDueDate = $onTime + $completedLate;
            $onTimeRate = $completedWithDueDate > 0 ? round(($onTime / $completedWithDueDate) * 100) : 100;

            $report[] = [
                'user_id' => $row->user_id,
                'name' => $row->name,
                'email' => $row->email,
                'avatar' => $row->avatar,
                'total_tasks' => $totalTasks,
                'completed' => $completed,
                'completed_today' => (int) $row->completed_today,
                'completed_this_week' => (int) $row->completed_this_week,
                'pending' => (int) $row->pending,
                'overdue' => (int) $row->overdue,
                'on_time' => $onTime,
                'completed_late' => $completedLate,
                'performance' => $performance,
                'on_time_rate' => $onTimeRate,
            ];
        }

        // Sort by performance descending
        usort($report, function ($a, $b) {
            return $b['performance'] <=> $a['performance'];
        });

        // Calculate team totals
        $teamTotals = [
            'total_tasks' => array_sum(array_column($report, 'total_tasks')),
            'completed' => array_sum(array_column($report, 'completed')),
            'pending' => array_sum(array_column($report, 'pending')),
            'overdue' => array_sum(array_column($report, 'overdue')),
            'on_time' => array_sum(array_column($report, 'on_time')),
        ];

        $teamTotals['performance'] = $teamTotals['total_tasks'] > 0
            ? round(($teamTotals['completed'] / $teamTotals['total_tasks']) * 100)
            : 0;

        return response()->json([
            'users' => $report,
            'team_totals' => $teamTotals,
            'period' => $period,
        ]);
    }
}
