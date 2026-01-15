<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
        'amount_deducted',
        'remaining_balance',
        'payroll_id',
        'expected_deduction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_deducted' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'approved_at' => 'datetime',
        'expected_deduction_date' => 'date',
    ];

    /**
     * Get the user who requested the advance
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved/rejected the request
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the payroll where this advance was deducted
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for deducted requests
     */
    public function scopeDeducted($query)
    {
        return $query->where('status', 'deducted');
    }

    /**
     * Scope for requests that need deduction (approved but not fully deducted)
     */
    public function scopeNeedsDeduction($query)
    {
        return $query->where('status', 'approved')
            ->whereColumn('amount_deducted', '<', 'amount');
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        })->orWhere('reason', 'like', "%{$search}%");
    }

    /**
     * Get the outstanding balance for this advance
     */
    public function getOutstandingBalanceAttribute()
    {
        return $this->amount - $this->amount_deducted;
    }

    /**
     * Check if this advance is fully deducted
     */
    public function isFullyDeducted()
    {
        return $this->amount_deducted >= $this->amount;
    }

    /**
     * Record a deduction from payroll
     */
    public function recordDeduction($amount, $payrollId = null)
    {
        $this->amount_deducted += $amount;
        $this->remaining_balance = $this->amount - $this->amount_deducted;

        if ($payrollId) {
            $this->payroll_id = $payrollId;
        }

        if ($this->isFullyDeducted()) {
            $this->status = 'deducted';
        }

        $this->save();
    }
}
