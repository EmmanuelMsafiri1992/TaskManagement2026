<?php

namespace App\Notifications;

use App\Models\JobShare;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewJobAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var JobShare
     */
    protected $jobShare;

    /**
     * @var Task
     */
    protected $task;

    /**
     * Create a new notification instance.
     *
     * @param  JobShare  $jobShare
     * @param  Task  $task
     * @return void
     */
    public function __construct(JobShare $jobShare, Task $task)
    {
        $this->jobShare = $jobShare;
        $this->task = $task;
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
        $taskUrl = url("/projects/{$this->task->project_id}/tasks/{$this->task->id}");

        return (new MailMessage)
            ->subject('New Job to Share: ' . $this->jobShare->post_title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new job has been assigned to you for social media sharing.')
            ->line('**Job Title:** ' . $this->jobShare->post_title)
            ->line('**Country:** ' . strtoupper($this->jobShare->country_code))
            ->line('The content has been pre-formatted and is ready to share:')
            ->line('```')
            ->line($this->jobShare->formatted_content)
            ->line('```')
            ->action('View Task', $taskUrl)
            ->line('Simply copy the formatted content and share it on your social media platforms.')
            ->line('Once you\'ve copied the content, mark the task as complete.')
            ->line('Thank you for your contribution!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $taskUrl = $this->task
            ? url("/projects/{$this->task->project_id}/tasks/{$this->task->id}")
            : url('/job-shares');

        return [
            'type' => 'job_assigned',
            'job_share_id' => $this->jobShare->id,
            'task_id' => $this->task->id,
            'post_title' => $this->jobShare->post_title,
            'country_code' => $this->jobShare->country_code,
            'shortened_url' => $this->jobShare->shortened_url,
            'formatted_content' => $this->jobShare->formatted_content,
            'task_url' => $taskUrl,
            'message' => "New job to share: {$this->jobShare->post_title}",
        ];
    }
}
