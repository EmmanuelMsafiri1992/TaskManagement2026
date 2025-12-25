<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'description',
        'is_recurring',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the user who created the holiday
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for upcoming holidays
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
                     ->orderBy('date', 'asc');
    }

    /**
     * Scope for past holidays
     */
    public function scopePast($query)
    {
        return $query->where('date', '<', now()->toDateString())
                     ->orderBy('date', 'desc');
    }

    /**
     * Scope for holidays in a specific year
     */
    public function scopeInYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }

    /**
     * Scope for holidays in a specific month
     */
    public function scopeInMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
                     ->whereMonth('date', $month);
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('description', 'like', "%{$search}%");
    }

    /**
     * Scope for recurring holidays
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }
}
