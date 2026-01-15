<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWorkingHours extends Model
{
    use HasFactory, Auditable;

    protected $table = 'user_working_hours';

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'effective_from',
        'effective_until',
        'is_active',
        'reason',
        'assigned_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns this working hours record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who assigned these working hours.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope to get only active working hours.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get currently effective working hours.
     */
    public function scopeCurrent($query)
    {
        $today = Carbon::today();

        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                // Permanent (no dates set)
                $q->where(function ($permanent) {
                    $permanent->whereNull('effective_from')
                              ->whereNull('effective_until');
                })
                // Or within date range
                ->orWhere(function ($ranged) use ($today) {
                    $ranged->where(function ($from) use ($today) {
                        $from->whereNull('effective_from')
                             ->orWhere('effective_from', '<=', $today);
                    })
                    ->where(function ($until) use ($today) {
                        $until->whereNull('effective_until')
                              ->orWhere('effective_until', '>=', $today);
                    });
                });
            });
    }

    /**
     * Scope to get working hours for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get expiring soon (within 7 days).
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        $today = Carbon::today();
        $futureDate = Carbon::today()->addDays($days);

        return $query->where('is_active', true)
            ->whereNotNull('effective_until')
            ->whereBetween('effective_until', [$today, $futureDate]);
    }

    /**
     * Get formatted start time.
     */
    public function getFormattedStartTimeAttribute()
    {
        return Carbon::parse($this->start_time)->format('g:i A');
    }

    /**
     * Get formatted end time.
     */
    public function getFormattedEndTimeAttribute()
    {
        return Carbon::parse($this->end_time)->format('g:i A');
    }

    /**
     * Get the duration in hours.
     */
    public function getDurationHoursAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $end->diffInHours($start);
    }

    /**
     * Check if this is a permanent assignment.
     */
    public function getIsPermanentAttribute()
    {
        return is_null($this->effective_from) && is_null($this->effective_until);
    }

    /**
     * Get the schedule type label.
     */
    public function getScheduleTypeAttribute()
    {
        if ($this->is_permanent) {
            return 'Permanent';
        }

        return 'Temporary';
    }

    /**
     * Get the effective period description.
     */
    public function getEffectivePeriodAttribute()
    {
        if ($this->is_permanent) {
            return 'Permanent';
        }

        $from = $this->effective_from ? $this->effective_from->format('M d, Y') : 'N/A';
        $until = $this->effective_until ? $this->effective_until->format('M d, Y') : 'Ongoing';

        return "{$from} - {$until}";
    }

    /**
     * Check if this working hours record is currently effective.
     */
    public function isCurrentlyEffective()
    {
        if (!$this->is_active) {
            return false;
        }

        $today = Carbon::today();

        if ($this->is_permanent) {
            return true;
        }

        $fromOk = is_null($this->effective_from) || $this->effective_from->lte($today);
        $untilOk = is_null($this->effective_until) || $this->effective_until->gte($today);

        return $fromOk && $untilOk;
    }
}
