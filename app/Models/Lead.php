<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'job_title',
        'website',
        'country',
        'city',
        'service_interest',
        'project_description',
        'budget_range',
        'timeline',
        'status',
        'priority',
        'score',
        'source',
        'source_detail',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'landing_page',
        'referrer_url',
        'ip_address',
        'user_agent',
        'assigned_to',
        'last_contacted_at',
        'next_follow_up_at',
        'internal_notes',
        'converted_to_client_id',
        'converted_at',
        'loss_reason',
    ];

    protected $casts = [
        'last_contacted_at' => 'datetime',
        'next_follow_up_at' => 'datetime',
        'converted_at' => 'datetime',
        'score' => 'integer',
    ];

    protected $appends = ['full_name', 'status_label', 'priority_color'];

    // Constants for status
    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_PROPOSAL_SENT = 'proposal_sent';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_WON = 'won';
    const STATUS_LOST = 'lost';

    // Constants for priority
    const PRIORITY_HOT = 'hot';
    const PRIORITY_WARM = 'warm';
    const PRIORITY_COLD = 'cold';

    // Service interests
    const SERVICE_INTERESTS = [
        'web_development' => 'Web Development',
        'mobile_app' => 'Mobile App Development',
        'software_development' => 'Software Development',
        'ui_ux_design' => 'UI/UX Design',
        'ecommerce' => 'E-Commerce Solutions',
        'maintenance' => 'Website Maintenance',
        'consulting' => 'IT Consulting',
        'other' => 'Other Services',
    ];

    // Budget ranges
    const BUDGET_RANGES = [
        'under_500' => 'Under $500',
        '500_1000' => '$500 - $1,000',
        '1000_5000' => '$1,000 - $5,000',
        '5000_10000' => '$5,000 - $10,000',
        '10000_plus' => '$10,000+',
        'not_sure' => 'Not Sure Yet',
    ];

    // Timelines
    const TIMELINES = [
        'immediate' => 'Immediately',
        '1_month' => 'Within 1 Month',
        '1_3_months' => '1-3 Months',
        '3_6_months' => '3-6 Months',
        'flexible' => 'Flexible',
    ];

    // Sources
    const SOURCES = [
        'website' => 'Website',
        'referral' => 'Referral',
        'social_media' => 'Social Media',
        'google_ads' => 'Google Ads',
        'linkedin' => 'LinkedIn',
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'email_campaign' => 'Email Campaign',
        'cold_outreach' => 'Cold Outreach',
        'other' => 'Other',
    ];

    // Relationships
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_to_client_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'new' => 'New Lead',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal_sent' => 'Proposal Sent',
            'negotiation' => 'In Negotiation',
            'won' => 'Won',
            'lost' => 'Lost',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'hot' => 'red',
            'warm' => 'orange',
            'cold' => 'blue',
            default => 'gray',
        };
    }

    public function getServiceInterestLabelAttribute(): string
    {
        return self::SERVICE_INTERESTS[$this->service_interest] ?? $this->service_interest;
    }

    public function getBudgetRangeLabelAttribute(): string
    {
        return self::BUDGET_RANGES[$this->budget_range] ?? $this->budget_range;
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_WON, self::STATUS_LOST]);
    }

    public function scopeHot($query)
    {
        return $query->where('priority', self::PRIORITY_HOT);
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->where('next_follow_up_at', '<=', now())
                     ->whereNotIn('status', [self::STATUS_WON, self::STATUS_LOST]);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Methods
    public function calculateScore(): int
    {
        $score = 0;

        // Budget scoring
        $budgetScores = [
            'under_500' => 5,
            '500_1000' => 15,
            '1000_5000' => 25,
            '5000_10000' => 35,
            '10000_plus' => 50,
            'not_sure' => 10,
        ];
        $score += $budgetScores[$this->budget_range] ?? 0;

        // Timeline scoring (urgency)
        $timelineScores = [
            'immediate' => 30,
            '1_month' => 25,
            '1_3_months' => 15,
            '3_6_months' => 10,
            'flexible' => 5,
        ];
        $score += $timelineScores[$this->timeline] ?? 0;

        // Company info completeness
        if ($this->company_name) $score += 5;
        if ($this->phone) $score += 5;
        if ($this->website) $score += 5;
        if ($this->project_description && strlen($this->project_description) > 50) $score += 5;

        return min($score, 100);
    }

    public function updateScore(): self
    {
        $this->score = $this->calculateScore();
        $this->save();

        return $this;
    }

    public function markAsContacted(): self
    {
        $this->status = self::STATUS_CONTACTED;
        $this->last_contacted_at = now();
        $this->save();

        return $this;
    }

    public function convertToClient(): ?Client
    {
        if ($this->converted_to_client_id) {
            return $this->convertedClient;
        }

        $client = Client::create([
            'name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'website' => $this->website,
            'country' => $this->country,
            'city' => $this->city,
            'status' => 'active',
            'notes' => "Converted from lead on " . now()->format('Y-m-d') . "\n\nOriginal inquiry: " . $this->project_description,
        ]);

        $this->converted_to_client_id = $client->id;
        $this->converted_at = now();
        $this->status = self::STATUS_WON;
        $this->save();

        return $client;
    }

    public function logActivity(string $type, ?string $subject = null, ?string $description = null, ?array $metadata = null, ?int $userId = null): LeadActivity
    {
        return $this->activities()->create([
            'type' => $type,
            'subject' => $subject,
            'description' => $description,
            'metadata' => $metadata,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}
