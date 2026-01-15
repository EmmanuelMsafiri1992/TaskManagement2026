<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadEmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const TYPE_INITIAL_CONTACT = 'initial_contact';
    const TYPE_FOLLOW_UP = 'follow_up';
    const TYPE_PROPOSAL = 'proposal';
    const TYPE_THANK_YOU = 'thank_you';
    const TYPE_CUSTOM = 'custom';

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function renderForLead(Lead $lead): array
    {
        $replacements = [
            '{{first_name}}' => $lead->first_name,
            '{{last_name}}' => $lead->last_name,
            '{{full_name}}' => $lead->full_name,
            '{{email}}' => $lead->email,
            '{{company_name}}' => $lead->company_name ?? 'your company',
            '{{service_interest}}' => $lead->service_interest_label ?? 'our services',
            '{{website}}' => 'emphxs.com',
        ];

        $subject = str_replace(array_keys($replacements), array_values($replacements), $this->subject);
        $body = str_replace(array_keys($replacements), array_values($replacements), $this->body);

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
