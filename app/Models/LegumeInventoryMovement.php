<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegumeInventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'legume_inventory_movements';

    protected $fillable = [
        'legume_product_id',
        'type',
        'quantity',
        'unit_cost',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'balance_after' => 'decimal:3',
    ];

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(LegumeProduct::class, 'legume_product_id');
    }

    /**
     * Get the user who made this movement
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reference (polymorphic)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scope for purchases
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    /**
     * Scope for sales
     */
    public function scopeSales($query)
    {
        return $query->where('type', 'sale');
    }

    /**
     * Scope for adjustments
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
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
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get type badge styling
     */
    public function getTypeBadgeAttribute()
    {
        $badges = [
            'purchase' => 'bg-green-100 text-green-800',
            'sale' => 'bg-blue-100 text-blue-800',
            'adjustment' => 'bg-yellow-100 text-yellow-800',
            'damage' => 'bg-red-100 text-red-800',
            'return' => 'bg-purple-100 text-purple-800',
        ];

        return $badges[$this->type] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Check if this is an incoming movement
     */
    public function isIncoming()
    {
        return $this->quantity > 0;
    }

    /**
     * Check if this is an outgoing movement
     */
    public function isOutgoing()
    {
        return $this->quantity < 0;
    }
}
