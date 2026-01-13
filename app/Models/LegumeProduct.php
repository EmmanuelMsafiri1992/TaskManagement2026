<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegumeProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'unit',
        'buying_price',
        'selling_price',
        'low_stock_threshold',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'buying_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = ['current_stock', 'profit_margin'];

    /**
     * Get the inventory for this product
     */
    public function inventory()
    {
        return $this->hasOne(LegumeInventory::class);
    }

    /**
     * Get all purchases of this product
     */
    public function purchases()
    {
        return $this->hasMany(LegumePurchase::class);
    }

    /**
     * Get all order items for this product
     */
    public function orderItems()
    {
        return $this->hasMany(LegumeOrderItem::class);
    }

    /**
     * Get inventory movements for this product
     */
    public function movements()
    {
        return $this->hasMany(LegumeInventoryMovement::class);
    }

    /**
     * Get stock alerts for this product
     */
    public function stockAlerts()
    {
        return $this->hasMany(LegumeStockAlert::class);
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('inventory', function ($q) {
            $q->whereRaw('quantity <= legume_products.low_stock_threshold');
        });
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get current stock level
     */
    public function getCurrentStockAttribute()
    {
        return $this->inventory?->quantity ?? 0;
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginAttribute()
    {
        if ($this->buying_price > 0) {
            return round((($this->selling_price - $this->buying_price) / $this->buying_price) * 100, 2);
        }
        return 0;
    }

    /**
     * Get available stock (current - reserved)
     */
    public function getAvailableStockAttribute()
    {
        $inventory = $this->inventory;
        if (!$inventory) {
            return 0;
        }
        return $inventory->quantity - $inventory->reserved_quantity;
    }

    /**
     * Generate unique SKU
     */
    public static function generateSku($name)
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $count = self::where('sku', 'like', $prefix . '%')->count() + 1;
        return $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
