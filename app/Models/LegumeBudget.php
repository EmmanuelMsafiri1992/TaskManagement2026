<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegumeBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'type',
        'description',
        'budget_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'budget_date' => 'date',
    ];

    /**
     * Get the user who created this budget entry
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for date range
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('budget_date', [$startDate, $endDate]);
    }

    /**
     * Get current available budget (sum of all entries)
     */
    public static function getCurrentBudget()
    {
        return self::sum('amount');
    }

    /**
     * Get total additions
     */
    public static function getTotalAdditions()
    {
        return self::whereIn('type', ['initial', 'addition'])->sum('amount');
    }

    /**
     * Get total deductions
     */
    public static function getTotalDeductions()
    {
        return abs(self::whereIn('type', ['deduction'])->sum('amount'));
    }

    /**
     * Add to budget
     */
    public static function addBudget($amount, $type, $description = null, $userId = null)
    {
        $budgetAmount = in_array($type, ['deduction']) ? -abs($amount) : abs($amount);

        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'amount' => $budgetAmount,
            'type' => $type,
            'description' => $description,
            'budget_date' => now()->toDateString(),
        ]);
    }

    /**
     * Get type badge styling
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'initial' => 'bg-blue-100 text-blue-800',
            'addition' => 'bg-green-100 text-green-800',
            'deduction' => 'bg-red-100 text-red-800',
            'adjustment' => 'bg-yellow-100 text-yellow-800',
        ];

        return $badges[$this->type] ?? 'bg-gray-100 text-gray-800';
    }
}
