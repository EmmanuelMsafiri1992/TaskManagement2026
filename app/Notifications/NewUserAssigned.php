<?php

namespace App\Notifications;

use App\Models\UserAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $assignment;

    /**
     * Create a new notification instance.
     *
     * @param  UserAssignment  $assignment
     * @return void
     */
    public function __construct(UserAssignment $assignment)
    {
        $this->assignment = $assignment;
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
        $userType = $this->assignment->getUserTypeName();
        $taskUrl = $this->assignment->task_id
            ? url('/projects/' . $this->assignment->task->project_id . '/tasks/' . $this->assignment->task_id)
            : url('/settings/user-assignments');

        return (new MailMessage)
            ->subject('New ' . $userType . ' Assigned to You')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new ' . $userType . ' has been assigned to you.')
            ->line('**Name:** ' . $this->assignment->v11_user_name)
            ->line('**Email:** ' . $this->assignment->v11_user_email)
            ->line('You can now follow up with this user by sending them an email and engaging with them.')
            ->action('View Task', $taskUrl)
            ->line('Thank you for your work!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $url = $this->assignment->task_id
            ? '/projects/' . $this->assignment->task->project_id . '/tasks/' . $this->assignment->task_id
            : '/settings/user-assignments';

        return [
            'type' => 'user_assigned',
            'message' => 'New ' . $this->assignment->getUserTypeName() . ' assigned: ' . $this->assignment->v11_user_name,
            'assignment_id' => $this->assignment->id,
            'task_id' => $this->assignment->task_id,
            'v11_user_id' => $this->assignment->v11_user_id,
            'v11_user_type' => $this->assignment->v11_user_type,
            'v11_user_name' => $this->assignment->v11_user_name,
            'v11_user_email' => $this->assignment->v11_user_email,
            'user_type_name' => $this->assignment->getUserTypeName(),
            'url' => $url,
        ];
    }
}
