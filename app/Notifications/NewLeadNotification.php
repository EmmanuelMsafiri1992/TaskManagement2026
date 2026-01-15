<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Lead $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $priorityEmoji = match($this->lead->priority) {
            'hot' => '🔥',
            'warm' => '🌡️',
            'cold' => '❄️',
            default => '📩',
        };

        return (new MailMessage)
            ->subject("{$priorityEmoji} New Lead: {$this->lead->full_name} - {$this->lead->service_interest_label}")
            ->greeting("New Lead Received!")
            ->line("A new potential client has submitted an inquiry on emphxs.com")
            ->line("**Contact Details:**")
            ->line("- **Name:** {$this->lead->full_name}")
            ->line("- **Email:** {$this->lead->email}")
            ->line("- **Phone:** " . ($this->lead->phone ?: 'Not provided'))
            ->line("- **Company:** " . ($this->lead->company_name ?: 'Not provided'))
            ->line("")
            ->line("**Project Details:**")
            ->line("- **Service:** {$this->lead->service_interest_label}")
            ->line("- **Budget:** " . ($this->lead->budget_range_label ?? 'Not specified'))
            ->line("- **Timeline:** " . (Lead::TIMELINES[$this->lead->timeline] ?? 'Not specified'))
            ->line("")
            ->line("**Project Description:**")
            ->line($this->lead->project_description ?? 'No description provided')
            ->line("")
            ->line("**Lead Score:** {$this->lead->score}/100 | **Priority:** " . ucfirst($this->lead->priority))
            ->action('View Lead Details', url('/admin/leads/' . $this->lead->id))
            ->line("Please follow up with this lead within 24 hours for best results.");
    }

    public function toArray($notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'type' => 'new_lead',
            'title' => 'New Lead: ' . $this->lead->full_name,
            'message' => "New {$this->lead->priority} priority lead from {$this->lead->email} interested in {$this->lead->service_interest_label}",
            'priority' => $this->lead->priority,
            'service_interest' => $this->lead->service_interest,
            'url' => '/admin/leads/' . $this->lead->id,
        ];
    }
}
