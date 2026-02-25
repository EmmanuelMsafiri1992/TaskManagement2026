<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders
                            {--hours=24 : Hours after which to send reminder}
                            {--project=18 : Project ID to check for pending tasks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to users with uncompleted tasks past due time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $projectId = (int) $this->option('project');

        $this->info("Checking for uncompleted tasks older than {$hours} hours in project {$projectId}...");

        // Get all users with pending tasks that are past due
        $usersWithPendingTasks = $this->getUsersWithPendingTasks($hours, $projectId);

        if ($usersWithPendingTasks->isEmpty()) {
            $this->info('No pending tasks found that need reminders.');
            return Command::SUCCESS;
        }

        $this->info("Found {$usersWithPendingTasks->count()} user(s) with pending tasks.");

        $remindersSent = 0;

        foreach ($usersWithPendingTasks as $userId => $tasks) {
            $user = User::find($userId);

            if (!$user) {
                continue;
            }

            // Check if we already sent a reminder today
            $recentReminder = $user->notifications()
                ->where('type', TaskReminder::class)
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();

            if ($recentReminder) {
                $this->line("  - Skipping {$user->name}: already reminded today");
                continue;
            }

            try {
                $user->notify(new TaskReminder($tasks));
                $remindersSent++;

                $this->line("  - Sent reminder to {$user->name} ({$tasks->count()} pending tasks)");

                Log::info('Task reminder sent', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'pending_tasks' => $tasks->count(),
                ]);
            } catch (\Exception $e) {
                $this->error("  - Failed to send reminder to {$user->name}: {$e->getMessage()}");

                Log::error('Failed to send task reminder', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Sent {$remindersSent} reminder(s).");

        return Command::SUCCESS;
    }

    /**
     * Get users with pending tasks that are past due.
     *
     * @param  int  $hours
     * @param  int  $projectId
     * @return \Illuminate\Support\Collection
     */
    protected function getUsersWithPendingTasks(int $hours, int $projectId)
    {
        // Get tasks that:
        // - Are not completed
        // - Belong to the specified project
        // - Were created more than X hours ago OR are past due date
        // - Have assigned users
        $pendingTasks = Task::whereNull('completed_at')
            ->where('project_id', $projectId)
            ->where(function ($query) use ($hours) {
                $query->where('created_at', '<=', now()->subHours($hours))
                    ->orWhere('due_at', '<=', now());
            })
            ->whereHas('users')
            ->with('users')
            ->get();

        // Group tasks by user
        $userTasks = collect();

        foreach ($pendingTasks as $task) {
            foreach ($task->users as $user) {
                if (!$userTasks->has($user->id)) {
                    $userTasks[$user->id] = collect();
                }
                $userTasks[$user->id]->push($task);
            }
        }

        return $userTasks;
    }
}
