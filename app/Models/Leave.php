<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'is_half_day',
        'half_day_period',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'decimal:1',
        'is_half_day' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user who requested the leave
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved/rejected the leave
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending leaves
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved leaves
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected leaves
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for leave type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('leave_type', $type);
    }

    /**
     * Scope for date range
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        })->orWhere('leave_type', 'like', "%{$search}%")
          ->orWhere('reason', 'like', "%{$search}%");
    }
}
