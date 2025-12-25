<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payroll_period',
        'period_start',
        'period_end',
        'basic_salary',
        'allowances',
        'bonuses',
        'deductions',
        'gross_salary',
        'net_salary',
        'currency',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'paid_at',
        'sent_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        })->orWhere('payroll_period', 'like', "%{$search}%");
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('period_start', [$startDate, $endDate])
              ->orWhereBetween('period_end', [$startDate, $endDate]);
        });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Calculate totals based on basic salary, allowances, bonuses, and deductions
     */
    public function calculateTotals()
    {
        $this->gross_salary = $this->basic_salary + $this->allowances + $this->bonuses;
        $this->net_salary = $this->gross_salary - $this->deductions;
        $this->save();
    }
}
