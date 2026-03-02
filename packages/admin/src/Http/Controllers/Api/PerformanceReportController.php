<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        $period = $request->get('period', 'week'); // week, month, all
        $today = Carbon::today();

        switch ($period) {
            case 'today':
                $startDate = $today;
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                break;
            default:
                $startDate = null; // All time
        }

        // Get all active users (not archived)
        $users = User::whereNotNull('email_verified_at')
            ->whereNull('archived_at')
            ->orderBy('name')
            ->get();

        $report = [];

        foreach ($users as $user) {
            $userStats = $this->getUserStats($user, $startDate, $today);
            $report[] = $userStats;
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

    /**
     * Get stats for a specific user.
     */
    private function getUserStats(User $user, ?Carbon $startDate, Carbon $today): array
    {
        $query = Task::whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $totalTasks = (clone $query)->count();

        $completed = (clone $query)->whereNotNull('completed_at')->count();

        $pending = (clone $query)->whereNull('completed_at')->count();

        $overdue = (clone $query)
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', $today)
            ->count();

        // Tasks completed on time (completed before or on due date)
        $onTime = (clone $query)
            ->whereNotNull('completed_at')
            ->whereNotNull('due_at')
            ->whereRaw('DATE(completed_at) <= DATE(due_at)')
            ->count();

        // Tasks completed late
        $completedLate = (clone $query)
            ->whereNotNull('completed_at')
            ->whereNotNull('due_at')
            ->whereRaw('DATE(completed_at) > DATE(due_at)')
            ->count();

        // Performance percentage
        $performance = $totalTasks > 0 ? round(($completed / $totalTasks) * 100) : 0;

        // On-time rate (of completed tasks with due dates)
        $completedWithDueDate = $onTime + $completedLate;
        $onTimeRate = $completedWithDueDate > 0 ? round(($onTime / $completedWithDueDate) * 100) : 100;

        // Completed today
        $completedToday = Task::whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereDate('completed_at', $today)->count();

        // Completed this week
        $startOfWeek = Carbon::now()->startOfWeek();
        $completedThisWeek = Task::whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('completed_at', '>=', $startOfWeek)->count();

        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'total_tasks' => $totalTasks,
            'completed' => $completed,
            'completed_today' => $completedToday,
            'completed_this_week' => $completedThisWeek,
            'pending' => $pending,
            'overdue' => $overdue,
            'on_time' => $onTime,
            'completed_late' => $completedLate,
            'performance' => $performance,
            'on_time_rate' => $onTimeRate,
        ];
    }
}
