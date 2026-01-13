<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegumeInventory extends Model
{
    use HasFactory;

    protected $table = 'legume_inventory';

    protected $fillable = [
        'legume_product_id',
        'quantity',
        'reserved_quantity',
        'average_cost',
        'last_restocked_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'average_cost' => 'decimal:2',
        'last_restocked_at' => 'datetime',
    ];

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(LegumeProduct::class, 'legume_product_id');
    }

    /**
     * Get available quantity (quantity - reserved)
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    /**
     * Get total inventory value
     */
    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->average_cost;
    }

    /**
     * Check if stock is low
     */
    public function isLowStock()
    {
        $threshold = $this->product->low_stock_threshold ?? 10;
        return $this->quantity <= $threshold;
    }

    /**
     * Check if out of stock
     */
    public function isOutOfStock()
    {
        return $this->quantity <= 0;
    }

    /**
     * Scope for low stock
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->whereRaw('legume_inventory.quantity <= legume_products.low_stock_threshold');
        });
    }

    /**
     * Scope for out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Scope for in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }
}
