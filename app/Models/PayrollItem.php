<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'item_type',
        'description',
        'amount',
        'category',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Scopes
     */
    public function scopeAllowances($query)
    {
        return $query->where('item_type', 'allowance');
    }

    public function scopeBonuses($query)
    {
        return $query->where('item_type', 'bonus');
    }

    public function scopeDeductions($query)
    {
        return $query->where('item_type', 'deduction');
    }
}
