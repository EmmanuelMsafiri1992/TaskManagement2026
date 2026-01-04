<?php

namespace App\Notifications;

use App\Models\UserWorkingHours;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class WorkingHoursAssigned extends Notification
{
    use Queueable;

    public UserWorkingHours $workingHours;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserWorkingHours $workingHours)
    {
        $this->workingHours = $workingHours;
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
        $schedule = $this->workingHours->formatted_start_time . ' - ' . $this->workingHours->formatted_end_time;
        $effectivePeriod = $this->workingHours->effective_period;

        $mailMessage = (new MailMessage)
            ->subject(__('Your Working Hours Have Been Updated'))
            ->greeting(__('Hi,') . ' ' . $notifiable->name)
            ->line(new HtmlString(__('Your working hours have been updated.')))
            ->line(new HtmlString('<strong>' . __('New Schedule') . ':</strong> ' . $schedule))
            ->line(new HtmlString('<strong>' . __('Effective Period') . ':</strong> ' . $effectivePeriod));

        if ($this->workingHours->reason) {
            $mailMessage->line(new HtmlString('<strong>' . __('Reason') . ':</strong> ' . $this->workingHours->reason));
        }

        $mailMessage->line(__('Please make note of this change and adjust your schedule accordingly.'))
            ->action(__('View Profile'), url('/profile'));

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $schedule = $this->workingHours->formatted_start_time . ' - ' . $this->workingHours->formatted_end_time;

        return [
            'item_id' => $this->workingHours->id,
            'type' => 'working_hours',
            'message' => __('Your working hours have been updated to') . ': ' . $schedule,
            'schedule' => $schedule,
            'effective_period' => $this->workingHours->effective_period,
            'is_permanent' => $this->workingHours->is_permanent,
        ];
    }
}
