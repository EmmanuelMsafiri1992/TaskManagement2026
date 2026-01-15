<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'subject',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = ['type_label', 'type_icon'];

    const TYPE_CREATED = 'created';
    const TYPE_EMAIL_SENT = 'email_sent';
    const TYPE_EMAIL_OPENED = 'email_opened';
    const TYPE_EMAIL_CLICKED = 'email_clicked';
    const TYPE_CALL_MADE = 'call_made';
    const TYPE_CALL_RECEIVED = 'call_received';
    const TYPE_MEETING_SCHEDULED = 'meeting_scheduled';
    const TYPE_MEETING_COMPLETED = 'meeting_completed';
    const TYPE_PROPOSAL_SENT = 'proposal_sent';
    const TYPE_PROPOSAL_VIEWED = 'proposal_viewed';
    const TYPE_STATUS_CHANGED = 'status_changed';
    const TYPE_NOTE_ADDED = 'note_added';
    const TYPE_FOLLOW_UP_SCHEDULED = 'follow_up_scheduled';
    const TYPE_WEBSITE_VISIT = 'website_visit';
    const TYPE_FORM_SUBMISSION = 'form_submission';

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'created' => 'Lead Created',
            'email_sent' => 'Email Sent',
            'email_opened' => 'Email Opened',
            'email_clicked' => 'Email Link Clicked',
            'call_made' => 'Call Made',
            'call_received' => 'Call Received',
            'meeting_scheduled' => 'Meeting Scheduled',
            'meeting_completed' => 'Meeting Completed',
            'proposal_sent' => 'Proposal Sent',
            'proposal_viewed' => 'Proposal Viewed',
            'status_changed' => 'Status Changed',
            'note_added' => 'Note Added',
            'follow_up_scheduled' => 'Follow-up Scheduled',
            'website_visit' => 'Website Visit',
            'form_submission' => 'Form Submitted',
        ];

        return $labels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getTypeIconAttribute(): string
    {
        $icons = [
            'created' => 'plus-circle',
            'email_sent' => 'mail',
            'email_opened' => 'mail-open',
            'email_clicked' => 'cursor-click',
            'call_made' => 'phone-outgoing',
            'call_received' => 'phone-incoming',
            'meeting_scheduled' => 'calendar',
            'meeting_completed' => 'check-circle',
            'proposal_sent' => 'document-text',
            'proposal_viewed' => 'eye',
            'status_changed' => 'refresh',
            'note_added' => 'annotation',
            'follow_up_scheduled' => 'clock',
            'website_visit' => 'globe',
            'form_submission' => 'clipboard-list',
        ];

        return $icons[$this->type] ?? 'information-circle';
    }
}
