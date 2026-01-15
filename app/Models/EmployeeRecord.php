<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'national_id',
        'phone_number',
        'physical_address',
        'date_of_birth',
        'gender',
        'marital_status',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_phone',
        'next_of_kin_address',
        'employment_date',
        'employment_type',
        'contract_start_date',
        'contract_end_date',
        'probation_period_months',
        'probation_end_date',
        'confirmation_date',
        'position',
        'department',
        'reports_to',
        'current_salary',
        'salary_currency',
        'employment_status',
        'termination_date',
        'termination_type',
        'termination_reason',
        'notice_period_days',
        'notice_given_date',
        'last_working_day',
        'annual_leave_days',
        'sick_leave_days',
        'maternity_leave_days',
        'leave_balance_annual',
        'leave_balance_sick',
        'tax_identification_number',
        'pension_number',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'employment_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'probation_end_date' => 'date',
        'confirmation_date' => 'date',
        'termination_date' => 'date',
        'notice_given_date' => 'date',
        'last_working_day' => 'date',
        'current_salary' => 'decimal:2',
        'leave_balance_annual' => 'decimal:2',
        'leave_balance_sick' => 'decimal:2',
        'probation_period_months' => 'integer',
        'notice_period_days' => 'integer',
        'annual_leave_days' => 'integer',
        'sick_leave_days' => 'integer',
        'maternity_leave_days' => 'integer',
    ];

    /**
     * Get the user that owns this employee record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supervisor (reports_to user).
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'reports_to');
    }

    /**
     * Get subordinates (employees who report to this employee's user).
     */
    public function subordinates()
    {
        return $this->hasMany(EmployeeRecord::class, 'reports_to', 'user_id');
    }

    /**
     * Get the user who created this record.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'Active');
    }

    /**
     * Scope for inactive employees.
     */
    public function scopeInactive($query)
    {
        return $query->whereIn('employment_status', ['Resigned', 'Terminated', 'Retired', 'Deceased']);
    }

    /**
     * Scope for employees on probation.
     */
    public function scopeOnProbation($query)
    {
        return $query->where('employment_type', 'Probation')
            ->where('employment_status', 'Active');
    }

    /**
     * Scope for filtering by department.
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope for searching employees.
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        })->orWhere('national_id', 'like', "%{$search}%")
          ->orWhere('phone_number', 'like', "%{$search}%")
          ->orWhere('position', 'like', "%{$search}%")
          ->orWhere('department', 'like', "%{$search}%");
    }
}
