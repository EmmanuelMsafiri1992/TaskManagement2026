<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class TaskReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Collection
     */
    protected $tasks;

    /**
     * Create a new notification instance.
     *
     * @param  Collection  $tasks
     * @return void
     */
    public function __construct(Collection $tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $taskCount = $this->tasks->count();
        $projectUrl = url('/projects/18'); // Nyasajob Career International project

        $message = (new MailMessage)
            ->subject("Reminder: {$taskCount} Pending Job(s) to Share")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("You have **{$taskCount} pending job(s)** that need to be shared on social media.")
            ->line('These tasks are past their due time. Please complete them as soon as possible.')
            ->line('---');

        // List up to 5 tasks in the email
        $tasksToShow = $this->tasks->take(5);
        foreach ($tasksToShow as $task) {
            $message->line("• **{$task->title}**");
        }

        if ($taskCount > 5) {
            $remaining = $taskCount - 5;
            $message->line("... and {$remaining} more task(s)");
        }

        $message->line('---')
            ->action('View All Tasks', $projectUrl)
            ->line('To complete a task:')
            ->line('1. Open the task')
            ->line('2. Click "Copy for Social Media"')
            ->line('3. Paste on your social media platforms')
            ->line('The task will be automatically marked as complete once copied.')
            ->line('Thank you for your contribution!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $taskCount = $this->tasks->count();

        return [
            'type' => 'task_reminder',
            'task_count' => $taskCount,
            'task_ids' => $this->tasks->pluck('id')->toArray(),
            'message' => "Reminder: You have {$taskCount} pending job(s) to share",
            'url' => url('/projects/18'),
        ];
    }
}
