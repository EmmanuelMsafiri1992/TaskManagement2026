<?php

namespace App\Console\Commands;

use App\Models\ChecklistItem;
use App\Models\Task;
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
    protected $description = 'Process recurring tasks and create new instances';

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
                $this->line("Processing task ID: {$task->id} - {$task->name}");

                $result = $this->createTask($task);

                if ($result === false) {
                    $this->warn("  → Skipped (completion required)");
                    $skipped++;
                } else {
                    $this->info("  → Processed successfully");
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
     * Create a task copy.
     *
     * @param  \App\Models\Task  $task
     * @return bool|null
     */
    protected function createTask($task)
    {
        $recurring = Recurring::make($task->meta['recurring']);

        $meta = $task->meta;

        // Check if task completion is required
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

        // Start Cloning
        $clone = $task->replicate();

        $nextIteration = $recurring->nextIteration();

        if ($nextIteration) {
            $meta['recurring'] = $recurring->pattern();
        } else {
            unset($meta['recurring']);
        }

        $clone->recurring_at = $nextIteration;
        $clone->meta = $meta;
        $clone->replicated_at = now();
        $clone->completed_at = null;
        $clone->due_at = null;
        $clone->total_seconds = 0;
        $clone->save();

        // Attach users
        $clone->users()->attach($task->users->pluck('id'));

        // For recurring tasks, always start with "not started" label
        // Remove status labels and only keep non-status labels
        $statusLabels = ['not started', 'started', 'in progress', 'completed', 'stuck'];
        $nonStatusLabels = $task->labels->filter(function ($label) use ($statusLabels) {
            return !in_array(strtolower($label->name), $statusLabels);
        })->pluck('id');

        // Attach non-status labels
        if ($nonStatusLabels->isNotEmpty()) {
            $clone->labels()->attach($nonStatusLabels);
        }

        // Always add "not started" label for new recurring task instance
        $notStartedLabel = \App\Models\Label::where('name', 'not started')->first();
        if ($notStartedLabel) {
            $clone->labels()->attach($notStartedLabel->id);
        }

        // Clone checklists
        foreach ($task->checklists as $checklist) {
            $cloneChecklist = $checklist->replicate();
            $cloneChecklist->task_id = $clone->id;
            $cloneChecklist->save();

            foreach ($checklist->checklistItems as $checklistItem) {
                ChecklistItem::create([
                    'checklist_id' => $cloneChecklist->id,
                    'title' => $checklistItem->title,
                    'order' => $checklistItem->order,
                ]);
            }
        }

        // Remove recurring from original task
        unset($meta['recurring']);
        $task->update([
            'recurring_at' => null,
            'meta' => $meta,
        ]);

        Log::info('Recurring task cloned', [
            'original_task_id' => $task->id,
            'new_task_id' => $clone->id,
            'next_iteration' => $nextIteration?->format('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
