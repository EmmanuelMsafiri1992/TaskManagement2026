<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegumeOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'legume_order_id',
        'legume_product_id',
        'quantity',
        'unit_price',
        'total',
        'cost_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->total = $item->quantity * $item->unit_price;
        });

        static::updating(function ($item) {
            $item->total = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            // Update order totals when item is saved
            $item->order->calculateTotals();
        });

        static::deleted(function ($item) {
            // Update order totals when item is deleted
            $item->order->calculateTotals();
        });
    }

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(LegumeOrder::class, 'legume_order_id');
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(LegumeProduct::class, 'legume_product_id');
    }

    /**
     * Get profit for this item
     */
    public function getProfitAttribute()
    {
        if ($this->cost_price) {
            return ($this->unit_price - $this->cost_price) * $this->quantity;
        }
        return null;
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginAttribute()
    {
        if ($this->cost_price && $this->cost_price > 0) {
            return round((($this->unit_price - $this->cost_price) / $this->cost_price) * 100, 2);
        }
        return null;
    }
}
