<?php

namespace Admin\Http\Controllers;

use App\Models\Label;
use App\Models\Task;
use App\Models\TaskCompletion;
use AhsanDev\Support\Recurring;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RecurringTasks
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $now = now();

        $tasks = Task::whereDate('recurring_at', $now->toDateString())
                    ->whereTime('recurring_at', $now->format('H:00'))
                    ->get();

        $processed = 0;
        $skipped = 0;

        foreach ($tasks as $task) {
            $result = $this->resetTask($task);
            if ($result === false) {
                $skipped++;
            } else {
                $processed++;
            }
        }

        return "Done! Processed: {$processed}, Skipped: {$skipped}";
    }

    /**
     * Reset the task for the next iteration instead of cloning.
     *
     * @param  \App\Models\Task  $task
     * @return bool|null
     */
    public function resetTask($task)
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
