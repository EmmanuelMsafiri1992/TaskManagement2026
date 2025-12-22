<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Label;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskLabel
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
        $label = Label::find($request->label);

        if (!$label) {
            return ['success' => false, 'message' => 'Label not found'];
        }

        // Toggle the label
        $status = $task->labels()->toggle($request->label);

        // Check if label was attached (added) or detached (removed)
        $wasAttached = !empty($status['attached']);

        // Sync task completion based on label change
        $this->syncTaskCompletion($task, $label, $wasAttached);

        // If this is a status label, remove other status labels
        if ($wasAttached && $this->isStatusLabel($label->name)) {
            $this->removeOtherStatusLabels($task, $label->id);

            // Send notifications for status changes (except "not started" and "completed")
            if (!in_array($label->name, ['not started', 'completed'])) {
                $this->sendStatusChangeNotification($task, $label);
            }
        }

        return ['success' => true, 'completed_at' => $task->fresh()->completed_at];
    }

    /**
     * Sync task completion status based on label
     */
    private function syncTaskCompletion(Task $task, Label $label, bool $wasAttached)
    {
        // If "completed" label was added, mark task as complete
        if ($label->name === 'completed' && $wasAttached) {
            $task->update(['completed_at' => now()]);
        }

        // If "completed" label was removed, mark task as incomplete
        if ($label->name === 'completed' && !$wasAttached) {
            $task->update(['completed_at' => null]);
        }

        // If any non-completed status label was added, mark task as incomplete
        if ($this->isStatusLabel($label->name) && $label->name !== 'completed' && $wasAttached) {
            $task->update(['completed_at' => null]);
        }
    }

    /**
     * Check if label is a status label
     */
    private function isStatusLabel(string $labelName): bool
    {
        return in_array($labelName, [
            'not started',
            'started',
            'in progress',
            'completed',
            'stuck'
        ]);
    }

    /**
     * Remove other status labels when a new status label is added
     */
    private function removeOtherStatusLabels(Task $task, int $currentLabelId)
    {
        $otherStatusLabels = Label::whereIn('name', [
            'not started',
            'started',
            'in progress',
            'completed',
            'stuck'
        ])
        ->where('id', '!=', $currentLabelId)
        ->pluck('id')
        ->toArray();

        $task->labels()->detach($otherStatusLabels);
    }

    /**
     * Send notification for status change
     */
    private function sendStatusChangeNotification(Task $task, Label $label)
    {
        $currentUser = auth()->user();

        // Get the task creator
        $creator = \App\Models\User::find($task->user_id ?? null);

        // Get all users assigned to the task
        $assignedUsers = $task->users;

        // Determine who should receive the notification
        $recipients = collect();

        // If current user is changing status, notify creator and other assigned users
        foreach ($assignedUsers as $user) {
            if ($user->id !== $currentUser->id) {
                $recipients->push($user);
            }
        }

        // Also notify creator if they're not the one making the change
        if ($creator && $creator->id !== $currentUser->id && !$recipients->contains('id', $creator->id)) {
            $recipients->push($creator);
        }

        // Create notification for each recipient
        foreach ($recipients as $recipient) {
            \App\Models\Notification::create([
                'user_id' => $recipient->id,
                'type' => $label->name === 'stuck' ? 'task_stuck' : 'task_status_changed',
                'data' => json_encode([
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'status' => $label->name,
                    'changed_by' => $currentUser->name,
                    'urgent' => $label->name === 'stuck', // Mark as urgent if stuck
                ]),
            ]);
        }

        // For "stuck" status, we could also trigger a real-time event here
        // This would need Laravel Broadcasting setup
        if ($label->name === 'stuck') {
            // event(new \App\Events\TaskStuck($task, $currentUser));
        }
    }
}
