<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TaskAssigned extends Notification
{
    use Queueable;

    public $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
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
        if (Str::contains($notifiable->email, 'example.com') || ! option('email_config')) {
            return ['database', 'broadcast'];
        }

        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject(__('Task Assigned'))
                    ->greeting(__('Hi,').' '.$notifiable->name)
                    ->line(new HtmlString(__('New task assigned to you').': <strong>'.$this->task->title.'</strong>'))
                    ->action(__('My Tasks'), url('/tasks'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'item_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'message' => __('You have assigned to the task').': '.$this->task->title,
        ];
    }
}
