<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InactivityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'inactive_from',
        'inactive_until',
        'detected_at',
        'reason_type',
        'page_url',
        'page_title',
        'inactive_duration_minutes',
        'user_explanation',
        'acknowledged_at',
        'is_pending',
    ];

    protected $casts = [
        'inactive_from' => 'datetime',
        'inactive_until' => 'datetime',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'is_pending' => 'boolean',
    ];

    /**
     * Get the user that owns this inactivity report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get pending reports (not yet acknowledged).
     */
    public function scopePending($query)
    {
        return $query->where('is_pending', true);
    }

    /**
     * Scope to get reports for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get reports by reason type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('reason_type', $type);
    }

    /**
     * Acknowledge this report with an explanation.
     */
    public function acknowledge(string $explanation): bool
    {
        return $this->update([
            'user_explanation' => $explanation,
            'acknowledged_at' => now(),
            'is_pending' => false,
        ]);
    }

    /**
     * Get human-readable reason type.
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason_type) {
            'same_page' => 'Extended time on same page',
            'computer_inactive' => 'Computer inactive',
            'power_outage' => 'Possible power outage',
            'session_gap' => 'Session gap detected',
            default => 'Unknown',
        };
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->inactive_duration_minutes;

        if ($minutes < 60) {
            return $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        $result = $hours . ' hour' . ($hours !== 1 ? 's' : '');
        if ($remainingMinutes > 0) {
            $result .= ' ' . $remainingMinutes . ' minute' . ($remainingMinutes !== 1 ? 's' : '');
        }

        return $result;
    }
}
