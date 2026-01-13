<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegumeStockAlert extends Model
{
    use HasFactory;

    protected $table = 'legume_stock_alerts';

    protected $fillable = [
        'legume_product_id',
        'alert_type',
        'threshold_quantity',
        'current_quantity',
        'is_acknowledged',
        'acknowledged_by',
        'acknowledged_at',
    ];

    protected $casts = [
        'threshold_quantity' => 'decimal:3',
        'current_quantity' => 'decimal:3',
        'is_acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(LegumeProduct::class, 'legume_product_id');
    }

    /**
     * Get the user who acknowledged this alert
     */
    public function acknowledgedByUser()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Scope for unacknowledged alerts
     */
    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    /**
     * Scope for acknowledged alerts
     */
    public function scopeAcknowledged($query)
    {
        return $query->where('is_acknowledged', true);
    }

    /**
     * Scope for low stock alerts
     */
    public function scopeLowStock($query)
    {
        return $query->where('alert_type', 'low_stock');
    }

    /**
     * Scope for out of stock alerts
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('alert_type', 'out_of_stock');
    }

    /**
     * Scope for filtering by alert type
     */
    public function scopeAlertType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    /**
     * Get alert type badge styling
     */
    public function getAlertTypeBadgeAttribute()
    {
        $badges = [
            'low_stock' => 'bg-yellow-100 text-yellow-800',
            'out_of_stock' => 'bg-red-100 text-red-800',
            'overstock' => 'bg-blue-100 text-blue-800',
        ];

        return $badges[$this->alert_type] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get alert type label
     */
    public function getAlertTypeLabelAttribute()
    {
        $labels = [
            'low_stock' => 'Low Stock',
            'out_of_stock' => 'Out of Stock',
            'overstock' => 'Overstock',
        ];

        return $labels[$this->alert_type] ?? ucfirst(str_replace('_', ' ', $this->alert_type));
    }

    /**
     * Acknowledge this alert
     */
    public function acknowledge($userId = null)
    {
        $this->is_acknowledged = true;
        $this->acknowledged_by = $userId ?? auth()->id();
        $this->acknowledged_at = now();
        $this->save();
    }

    /**
     * Create alert for product if needed
     */
    public static function createIfNeeded($productId)
    {
        $product = LegumeProduct::with('inventory')->find($productId);
        if (!$product) {
            return null;
        }

        $currentStock = $product->inventory?->quantity ?? 0;
        $threshold = $product->low_stock_threshold;

        // Delete existing unacknowledged alerts for this product
        self::where('legume_product_id', $productId)
            ->where('is_acknowledged', false)
            ->delete();

        if ($currentStock <= 0) {
            return self::create([
                'legume_product_id' => $productId,
                'alert_type' => 'out_of_stock',
                'threshold_quantity' => 0,
                'current_quantity' => $currentStock,
            ]);
        } elseif ($currentStock <= $threshold) {
            return self::create([
                'legume_product_id' => $productId,
                'alert_type' => 'low_stock',
                'threshold_quantity' => $threshold,
                'current_quantity' => $currentStock,
            ]);
        }

        return null;
    }
}
