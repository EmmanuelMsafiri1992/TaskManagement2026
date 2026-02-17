<?php

namespace App\Console\Commands;

use App\Models\Label;
use App\Models\Task;
use App\Models\TaskCompletion;
use AhsanDev\Support\Recurring;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRecurringTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process recurring tasks and reset them for the next iteration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $startTime = now();
        $this->info('Starting recurring tasks processor at ' . $startTime->format('Y-m-d H:i:s'));

        $now = now();

        // Find tasks that are scheduled to recur at this hour
        $tasks = Task::whereDate('recurring_at', $now->toDateString())
                    ->whereTime('recurring_at', $now->format('H:00'))
                    ->get();

        $this->info("Found {$tasks->count()} task(s) to process");

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($tasks as $task) {
            try {
                $this->line("Processing task ID: {$task->id} - {$task->title}");

                $result = $this->resetTask($task);

                if ($result === false) {
                    $this->warn("  → Skipped (completion required but not completed)");
                    $skipped++;
                } else {
                    $this->info("  → Reset successfully for next iteration");
                    $processed++;
                }
            } catch (\Exception $e) {
                $this->error("  → Failed: " . $e->getMessage());
                Log::error('Recurring task processing failed', [
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $failed++;
            }
        }

        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);

        $this->newLine();
        $this->info("Recurring tasks processing completed in {$duration}s");
        $this->table(
            ['Status', 'Count'],
            [
                ['Processed', $processed],
                ['Skipped', $skipped],
                ['Failed', $failed],
                ['Total', $tasks->count()],
            ]
        );

        Log::info('Recurring tasks processed', [
            'processed' => $processed,
            'skipped' => $skipped,
            'failed' => $failed,
            'total' => $tasks->count(),
            'duration' => $duration,
        ]);

        return Command::SUCCESS;
    }

    /**
     * Reset the task for the next iteration instead of cloning.
     *
     * @param  \App\Models\Task  $task
     * @return bool|null
     */
    protected function resetTask($task)
    {
        $recurring = Recurring::make($task->meta['recurring']);
        $meta = $task->meta;

        // Check if task completion is required but not completed
        if ($task->meta['recurring']['task_completion_required'] && ! $task->completed_at) {
            $recurring->updateNextDateWithoutIteration();
            $nextIteration = $recurring->nextIteration();
            $meta['recurring'] = $recurring->pattern();

            $task->update([
                'recurring_at' => $nextIteration,
                'meta' => $meta,
            ]);

            return false;
        }

        // Get current iteration number for notes
        $currentIteration = $meta['recurring']['current_iteration'] ?? 0;

        // Save completion history if task was completed
        if ($task->completed_at) {
            // Get the user who completed the task (first assigned user or null)
            $userId = $task->users->first()?->id;

            TaskCompletion::create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'completed_at' => $task->completed_at,
                'is_recurring' => true,
                'notes' => json_encode([
                    'iteration' => $currentIteration,
                    'total_seconds' => $task->total_seconds,
                    'labels' => $task->labels->pluck('name')->toArray(),
                ]),
            ]);
        }

        // Calculate next iteration
        $nextIteration = $recurring->nextIteration();

        // Update recurring pattern in meta
        if ($nextIteration) {
            $meta['recurring'] = $recurring->pattern();
        } else {
            // Recurring has ended (reached end date or max repetitions)
            unset($meta['recurring']);
        }

        // Reset checklist items to uncompleted
        foreach ($task->checklists as $checklist) {
            $checklist->checklistItems()->update(['completed_at' => null]);
        }

        // Reset labels to "not started"
        $statusLabels = ['not started', 'started', 'in progress', 'completed', 'stuck'];
        $nonStatusLabels = $task->labels->filter(function ($label) use ($statusLabels) {
            return !in_array(strtolower($label->name), $statusLabels);
        })->pluck('id')->toArray();

        // Detach all labels and reattach non-status labels
        $task->labels()->detach();
        if (!empty($nonStatusLabels)) {
            $task->labels()->attach($nonStatusLabels);
        }

        // Add "not started" label
        $notStartedLabel = Label::where('name', 'not started')->first();
        if ($notStartedLabel) {
            $task->labels()->attach($notStartedLabel->id);
        }

        // Reset the task for next iteration
        $task->update([
            'completed_at' => null,
            'total_seconds' => 0,
            'recurring_at' => $nextIteration,
            'meta' => $meta,
        ]);

        Log::info('Recurring task reset for next iteration', [
            'task_id' => $task->id,
            'iteration' => $currentIteration,
            'next_iteration' => $nextIteration?->format('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
