<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ServiceProvider extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guard = 'service_provider';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'national_id',
        'address',
        'specialty',
        'qualification',
        'status',
        'agreement_signed',
        'agreement_signed_at',
        'avatar',
        'hourly_rate',
        'bio',
        'meta',
        'total_agreed_amount',
        'payment_preference',
        'monthly_amount',
        'daily_rate',
        'amount_per_subject',
        'assigned_subjects_count',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_branch',
        'mobile_money_provider',
        'mobile_money_number',
        'mobile_money_name',
        'total_paid',
        'daily_subject_name',
        'daily_total_topics',
        'daily_payment_setup_complete',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'agreement_signed' => 'boolean',
        'agreement_signed_at' => 'datetime',
        'hourly_rate' => 'decimal:2',
        'total_agreed_amount' => 'decimal:2',
        'monthly_amount' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'amount_per_subject' => 'decimal:2',
        'assigned_subjects_count' => 'integer',
        'total_paid' => 'decimal:2',
        'meta' => 'array',
        'daily_total_topics' => 'integer',
        'daily_payment_setup_complete' => 'boolean',
    ];

    protected $appends = ['balance_remaining', 'payment_progress_percent', 'daily_payment_info'];

    public function agreements()
    {
        return $this->hasMany(ServiceProviderAgreement::class);
    }

    public function recordingSessions()
    {
        return $this->hasMany(RecordingSession::class);
    }

    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class);
    }

    /**
     * Get all subjects with the daily subject name (across all forms)
     */
    public function getDailySubjects()
    {
        if (!$this->daily_subject_name) {
            return collect();
        }

        return Subject::where('name', $this->daily_subject_name)
            ->where('is_active', true)
            ->orderBy('form')
            ->get();
    }

    /**
     * Get total topics count for the daily subject across all forms (1-4)
     */
    public function getDailySubjectTotalTopics(): int
    {
        if (!$this->daily_subject_name) {
            return 0;
        }

        return Topic::whereHas('subject', function ($query) {
            $query->where('name', $this->daily_subject_name)
                  ->where('is_active', true);
        })->where('is_active', true)->count();
    }

    public function latestAgreement()
    {
        return $this->hasOne(ServiceProviderAgreement::class)->latestOfMany();
    }

    public function hasSignedAgreement(): bool
    {
        return $this->agreement_signed;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getAvatarAttribute($value)
    {
        return $value ? '/' . $value : null;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function completedPayments()
    {
        return $this->hasMany(Payment::class)->where('status', 'completed');
    }

    public function getBalanceRemainingAttribute()
    {
        return $this->total_agreed_amount - $this->total_paid;
    }

    public function getPaymentProgressPercentAttribute()
    {
        if ($this->total_agreed_amount <= 0) {
            return 0;
        }
        return round(($this->total_paid / $this->total_agreed_amount) * 100, 1);
    }

    public function getFormattedTotalAgreedAttribute()
    {
        return 'MK ' . number_format($this->total_agreed_amount, 2);
    }

    public function getFormattedTotalPaidAttribute()
    {
        return 'MK ' . number_format($this->total_paid, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return 'MK ' . number_format($this->balance_remaining, 2);
    }

    public function getPaymentDetailsAttribute()
    {
        if ($this->payment_method === 'bank') {
            return [
                'type' => 'Bank Transfer',
                'bank' => $this->bank_name,
                'account' => $this->bank_account_number,
                'name' => $this->bank_account_name,
                'branch' => $this->bank_branch,
            ];
        } elseif ($this->payment_method === 'mobile_money') {
            return [
                'type' => 'Mobile Money',
                'provider' => $this->mobile_money_provider,
                'number' => $this->mobile_money_number,
                'name' => $this->mobile_money_name,
            ];
        }
        return null;
    }

    /**
     * Get assigned subjects for this service provider
     * Based on their recorded sessions or specialty
     */
    public function getAssignedSubjects()
    {
        return Subject::whereIn('id', function ($query) {
            $query->select('subject_id')
                ->from('recording_sessions')
                ->where('service_provider_id', $this->id)
                ->distinct();
        })->orWhere('name', 'like', '%' . $this->specialty . '%')->get();
    }

    /**
     * Get total topics count for assigned subjects
     */
    public function getTotalTopicsCount(): int
    {
        $subjectIds = $this->recordingSessions()->distinct()->pluck('subject_id')->toArray();

        if (empty($subjectIds)) {
            // Default to counting all active topics if no sessions yet
            return Topic::where('is_active', true)->count();
        }

        return Topic::whereIn('subject_id', $subjectIds)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get completed topics count (topics with approved recording sessions)
     */
    public function getCompletedTopicsCount(): int
    {
        return $this->recordingSessions()
            ->where('status', 'approved')
            ->distinct('topic_id')
            ->count('topic_id');
    }

    /**
     * Calculate maximum payable days based on daily rate and total budget
     * Formula: Total Budget / Daily Rate = Max Days
     */
    public function getMaxPayableDays(): int
    {
        if (!$this->daily_rate || $this->daily_rate <= 0) {
            return 0;
        }

        return (int) floor($this->total_agreed_amount / $this->daily_rate);
    }

    /**
     * Calculate maximum payable days per subject
     * Formula: Amount Per Subject / Daily Rate = Max Days Per Subject
     */
    public function getMaxPayableDaysPerSubject(): int
    {
        if (!$this->daily_rate || $this->daily_rate <= 0) {
            return 0;
        }

        return (int) floor($this->amount_per_subject / $this->daily_rate);
    }

    /**
     * Calculate required topics per day to complete all topics within budget
     * Formula: Total Topics / Max Payable Days = Topics Per Day
     */
    public function getRequiredTopicsPerDay(): float
    {
        $maxDays = $this->getMaxPayableDays();

        if ($maxDays <= 0) {
            return 0;
        }

        $totalTopics = $this->getTotalTopicsCount();

        return round($totalTopics / $maxDays, 2);
    }

    /**
     * Calculate required topics per day per subject
     */
    public function getRequiredTopicsPerDayPerSubject(): float
    {
        $maxDaysPerSubject = $this->getMaxPayableDaysPerSubject();

        if ($maxDaysPerSubject <= 0) {
            return 0;
        }

        // Get topics per subject (total / number of subjects)
        $topicsPerSubject = $this->getTotalTopicsCount() / max(1, $this->assigned_subjects_count);

        return round($topicsPerSubject / $maxDaysPerSubject, 2);
    }

    /**
     * Get remaining topics to complete
     */
    public function getRemainingTopicsCount(): int
    {
        return max(0, $this->getTotalTopicsCount() - $this->getCompletedTopicsCount());
    }

    /**
     * Get remaining payable days based on balance
     */
    public function getRemainingPayableDays(): int
    {
        if (!$this->daily_rate || $this->daily_rate <= 0) {
            return 0;
        }

        return (int) floor($this->balance_remaining / $this->daily_rate);
    }

    /**
     * Check if provider is on track (completed enough topics for days paid)
     */
    public function isOnTrack(): bool
    {
        if ($this->payment_preference !== 'daily' || !$this->daily_rate) {
            return true; // Not applicable for non-daily payment
        }

        $daysPaid = (int) floor($this->total_paid / $this->daily_rate);
        $expectedTopics = $daysPaid * $this->getRequiredTopicsPerDay();

        return $this->getCompletedTopicsCount() >= $expectedTopics;
    }

    /**
     * Get comprehensive daily payment information
     */
    public function getDailyPaymentInfoAttribute(): ?array
    {
        if ($this->payment_preference !== 'daily' || !$this->daily_rate) {
            return null;
        }

        $totalTopics = $this->getTotalTopicsCount();
        $completedTopics = $this->getCompletedTopicsCount();
        $maxDays = $this->getMaxPayableDays();
        $requiredTopicsPerDay = $this->getRequiredTopicsPerDay();
        $daysPaid = $this->daily_rate > 0 ? (int) floor($this->total_paid / $this->daily_rate) : 0;
        $remainingDays = $this->getRemainingPayableDays();
        $remainingTopics = $this->getRemainingTopicsCount();

        // Calculate if on track
        $expectedTopicsCompleted = $daysPaid * $requiredTopicsPerDay;
        $topicsAhead = $completedTopics - $expectedTopicsCompleted;
        $isOnTrack = $topicsAhead >= 0;

        // Calculate days worth of work completed
        $daysWorthCompleted = $requiredTopicsPerDay > 0
            ? round($completedTopics / $requiredTopicsPerDay, 1)
            : 0;

        return [
            'daily_rate' => $this->daily_rate,
            'formatted_daily_rate' => 'MK ' . number_format($this->daily_rate, 2),
            'total_topics' => $totalTopics,
            'completed_topics' => $completedTopics,
            'remaining_topics' => $remainingTopics,
            'max_payable_days' => $maxDays,
            'required_topics_per_day' => $requiredTopicsPerDay,
            'days_paid' => $daysPaid,
            'remaining_payable_days' => $remainingDays,
            'days_worth_completed' => $daysWorthCompleted,
            'expected_topics_completed' => round($expectedTopicsCompleted, 1),
            'topics_ahead_behind' => round($topicsAhead, 1),
            'is_on_track' => $isOnTrack,
            'status_message' => $isOnTrack
                ? ($topicsAhead > 0 ? "Ahead by " . abs(round($topicsAhead)) . " topics" : "On track")
                : "Behind by " . abs(round($topicsAhead)) . " topics",
            'amount_per_subject' => $this->amount_per_subject,
            'assigned_subjects_count' => $this->assigned_subjects_count,
        ];
    }

    /**
     * Calculate payment amount for daily rate based on topics completed today
     */
    public function calculateDailyPayment(int $topicsCompletedToday): array
    {
        if ($this->payment_preference !== 'daily' || !$this->daily_rate) {
            return [
                'eligible' => false,
                'reason' => 'Not on daily payment plan',
                'amount' => 0,
            ];
        }

        $requiredTopics = $this->getRequiredTopicsPerDay();

        if ($topicsCompletedToday < $requiredTopics) {
            return [
                'eligible' => false,
                'reason' => "Need to complete at least {$requiredTopics} topics per day. Completed: {$topicsCompletedToday}",
                'amount' => 0,
                'topics_needed' => ceil($requiredTopics) - $topicsCompletedToday,
            ];
        }

        // Check if there's balance remaining
        if ($this->balance_remaining < $this->daily_rate) {
            return [
                'eligible' => false,
                'reason' => 'Insufficient balance remaining',
                'amount' => $this->balance_remaining,
                'partial' => true,
            ];
        }

        return [
            'eligible' => true,
            'reason' => 'Eligible for full daily payment',
            'amount' => $this->daily_rate,
        ];
    }
}
