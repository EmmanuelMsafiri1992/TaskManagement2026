<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Label;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskComplete
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Task $task)
    {
        if ($task->completed_at) {
            // Uncomplete the task
            $task->update(['completed_at' => null]);

            // Remove "completed" label and optionally add "not started" label
            $this->syncLabelsForUncomplete($task);

            return ['success' => true, 'completed_at' => null];
        }

        // Complete the task
        $task->update([
            'completed_at' => now(),
        ]);

        // Add "completed" label and remove other status labels
        $this->syncLabelsForComplete($task);

        return ['success' => true, 'completed_at' => 'true'];
    }

    /**
     * Sync labels when task is marked as complete
     */
    private function syncLabelsForComplete(Task $task)
    {
        $completedLabel = Label::where('name', 'completed')->first();

        if (!$completedLabel) {
            return;
        }

        // Get all status labels (the ones we want to remove)
        $statusLabels = Label::whereIn('name', [
            'not started',
            'started',
            'in progress',
            'stuck'
        ])->pluck('id')->toArray();

        // Remove other status labels
        $task->labels()->detach($statusLabels);

        // Add completed label if not already attached
        if (!$task->labels()->where('label_id', $completedLabel->id)->exists()) {
            $task->labels()->attach($completedLabel->id);
        }
    }

    /**
     * Sync labels when task is marked as incomplete
     */
    private function syncLabelsForUncomplete(Task $task)
    {
        $completedLabel = Label::where('name', 'completed')->first();

        if ($completedLabel) {
            // Remove completed label
            $task->labels()->detach($completedLabel->id);
        }
    }
}
