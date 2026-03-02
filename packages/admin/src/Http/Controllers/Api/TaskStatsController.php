<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskStatsController
{
    /**
     * Get task statistics for the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $userId = auth()->id();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        // Tasks assigned to current user
        $userTasksQuery = Task::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        // Completed today
        $completedToday = (clone $userTasksQuery)
            ->whereDate('completed_at', $today)
            ->count();

        // Completed this week
        $completedThisWeek = (clone $userTasksQuery)
            ->where('completed_at', '>=', $startOfWeek)
            ->count();

        // Total completed (all time)
        $totalCompleted = (clone $userTasksQuery)
            ->whereNotNull('completed_at')
            ->count();

        // New tasks (assigned today)
        $newToday = (clone $userTasksQuery)
            ->whereDate('created_at', $today)
            ->whereNull('completed_at')
            ->count();

        // New tasks this week
        $newThisWeek = (clone $userTasksQuery)
            ->where('created_at', '>=', $startOfWeek)
            ->whereNull('completed_at')
            ->count();

        // Overdue tasks (due date passed, not completed)
        $overdue = (clone $userTasksQuery)
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', $today)
            ->count();

        // Pending tasks (not completed)
        $pending = (clone $userTasksQuery)
            ->whereNull('completed_at')
            ->count();

        // Total tasks assigned to user
        $total = Task::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();

        // Performance calculation (completed / total * 100)
        $performance = $total > 0 ? round(($totalCompleted / $total) * 100) : 0;

        // Weekly performance (completed this week / assigned this week)
        $assignedThisWeek = Task::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('created_at', '>=', $startOfWeek)->count();

        $weeklyPerformance = $assignedThisWeek > 0
            ? round(($completedThisWeek / $assignedThisWeek) * 100)
            : 0;

        return response()->json([
            'completed_today' => $completedToday,
            'completed_this_week' => $completedThisWeek,
            'total_completed' => $totalCompleted,
            'new_today' => $newToday,
            'new_this_week' => $newThisWeek,
            'overdue' => $overdue,
            'pending' => $pending,
            'total' => $total,
            'performance' => $performance,
            'weekly_performance' => $weeklyPerformance,
        ]);
    }
}
