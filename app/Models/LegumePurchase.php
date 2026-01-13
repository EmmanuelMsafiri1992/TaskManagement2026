<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegumePurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'legume_purchases';

    protected $fillable = [
        'purchase_number',
        'user_id',
        'supplier_id',
        'legume_product_id',
        'purchase_date',
        'quantity',
        'price_per_unit',
        'total_amount',
        'currency',
        'packaging_cost',
        'transport_cost',
        'other_costs',
        'grand_total',
        'quality_grade',
        'quality_notes',
        'status',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'quantity' => 'decimal:3',
        'price_per_unit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'packaging_cost' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'other_costs' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (!$purchase->purchase_number) {
                $purchase->purchase_number = self::generatePurchaseNumber();
            }
            // Calculate totals
            $purchase->total_amount = $purchase->quantity * $purchase->price_per_unit;
            $purchase->grand_total = $purchase->total_amount +
                ($purchase->packaging_cost ?? 0) +
                ($purchase->transport_cost ?? 0) +
                ($purchase->other_costs ?? 0);
        });

        static::updating(function ($purchase) {
            // Recalculate totals on update
            $purchase->total_amount = $purchase->quantity * $purchase->price_per_unit;
            $purchase->grand_total = $purchase->total_amount +
                ($purchase->packaging_cost ?? 0) +
                ($purchase->transport_cost ?? 0) +
                ($purchase->other_costs ?? 0);
        });
    }

    /**
     * Get the user who created this purchase
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(LegumeProduct::class, 'legume_product_id');
    }

    /**
     * Scope for pending purchases
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed purchases
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled purchases
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
     * Scope for date range
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('purchase_date', [$startDate, $endDate]);
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('purchase_number', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%")
              ->orWhereHas('supplier', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              })
              ->orWhereHas('product', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Generate purchase number
     */
    public static function generatePurchaseNumber()
    {
        $prefix = 'PUR-' . now()->format('Ym') . '-';
        $lastPurchase = self::where('purchase_number', 'like', $prefix . '%')
            ->orderBy('purchase_number', 'desc')
            ->first();

        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->purchase_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get status badge styling
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get quality grade badge styling
     */
    public function getGradeBadgeAttribute()
    {
        $badges = [
            'A' => 'bg-green-100 text-green-800',
            'B' => 'bg-yellow-100 text-yellow-800',
            'C' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->quality_grade] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get total additional costs
     */
    public function getAdditionalCostsAttribute()
    {
        return ($this->packaging_cost ?? 0) +
               ($this->transport_cost ?? 0) +
               ($this->other_costs ?? 0);
    }
}
