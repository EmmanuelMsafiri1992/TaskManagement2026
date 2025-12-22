<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CountryAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $countries;
    public $assignedBy;

    /**
     * Create a new notification instance.
     *
     * @param  array  $countries
     * @param  User  $assignedBy
     * @return void
     */
    public function __construct(array $countries, User $assignedBy)
    {
        $this->countries = $countries;
        $this->assignedBy = $assignedBy;
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
        $countryNames = collect($this->countries)->pluck('country_name')->join(', ');

        return (new MailMessage)
            ->subject('New Countries Assigned to You')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned to work on the following ' . (count($this->countries) > 1 ? 'countries' : 'country') . ':')
            ->line('**' . $countryNames . '**')
            ->line('Assigned by: ' . $this->assignedBy->name)
            ->action('View Your Countries', url('/settings/countries'))
            ->line('You can now view AdSense reports for these countries and manage the assigned websites.')
            ->line('Thank you!');
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
            'type' => 'country_assigned',
            'message' => count($this->countries) > 1
                ? 'You have been assigned to ' . count($this->countries) . ' new countries'
                : 'You have been assigned to ' . $this->countries[0]['country_name'],
            'countries' => $this->countries,
            'assigned_by' => [
                'id' => $this->assignedBy->id,
                'name' => $this->assignedBy->name,
            ],
            'url' => '/settings/countries',
        ];
    }
}
