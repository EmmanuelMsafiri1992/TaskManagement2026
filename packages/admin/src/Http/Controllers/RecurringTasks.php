<?php

namespace Admin\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\Task;
use AhsanDev\Support\Recurring;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        // temp
        // $knownDate = Carbon::create('2022-08-22 09:05');
        // $knownDate = Carbon::create('2022-08-27 00:15');
        // Carbon::setTestNow($knownDate);

        $now = now();

        $tasks = Task::whereDate('recurring_at', $now->toDateString())
                    ->whereTime('recurring_at', $now->format('H:00'))
                    ->get();

        foreach ($tasks as $task) {
            $this->createTask($task);
        }

        return 'Done!';
    }

    /**
     * Create a task copy.
     *
     * @return void
     */
    public function createTask($task)
    {
        $recurring = Recurring::make($task->meta['recurring']);

        $meta = $task->meta;

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

        // Start Cloning.
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

        unset($meta['recurring']);
        $task->update([
            'recurring_at' => null,
            'meta' => $meta,
        ]);
    }
}
