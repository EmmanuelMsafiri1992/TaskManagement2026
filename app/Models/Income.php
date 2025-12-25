<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $table = 'income';

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'income_date',
        'source',
        'category',
        'description',
        'invoice_number',
        'client_name',
        'quotation_id',
        'status',
        'received_by',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
        'received_at' => 'datetime',
    ];

    /**
     * Get the user who created the income
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who received/confirmed the income
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the related quotation
     */
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Scope for pending income
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for received income
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope for cancelled income
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by source
     */
    public function scopeSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('income_date', [$startDate, $endDate]);
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('source', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%")
              ->orWhere('invoice_number', 'like', "%{$search}%")
              ->orWhere('client_name', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%")
              ->orWhereHas('user', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get source badge styling
     */
    public function getSourceBadgeAttribute()
    {
        $badges = [
            'sales' => 'bg-blue-100 text-blue-800',
            'services' => 'bg-green-100 text-green-800',
            'consulting' => 'bg-purple-100 text-purple-800',
            'adsense' => 'bg-yellow-100 text-yellow-800',
            'quotation' => 'bg-indigo-100 text-indigo-800',
            'other' => 'bg-gray-100 text-gray-800',
        ];

        return $badges[$this->source] ?? 'bg-gray-100 text-gray-800';
    }
}
