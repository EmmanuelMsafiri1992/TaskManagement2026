<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegumeOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'legume_customer_id',
        'user_id',
        'order_date',
        'fulfillment_type',
        'subtotal',
        'discount_amount',
        'delivery_fee',
        'total_amount',
        'currency',
        'order_status',
        'payment_status',
        'amount_paid',
        'notes',
        'cancellation_reason',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Get the customer
     */
    public function customer()
    {
        return $this->belongsTo(LegumeCustomer::class, 'legume_customer_id');
    }

    /**
     * Get the user who created this order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get order items
     */
    public function items()
    {
        return $this->hasMany(LegumeOrderItem::class);
    }

    /**
     * Get payments for this order
     */
    public function payments()
    {
        return $this->hasMany(LegumeOrderPayment::class);
    }

    /**
     * Get delivery tracking
     */
    public function delivery()
    {
        return $this->hasOne(LegumeDeliveryTracking::class);
    }

    /**
     * Scope for order status
     */
    public function scopeOrderStatus($query, $status)
    {
        return $query->where('order_status', $status);
    }

    /**
     * Scope for payment status
     */
    public function scopePaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope for fulfillment type
     */
    public function scopeFulfillmentType($query, $type)
    {
        return $query->where('fulfillment_type', $type);
    }

    /**
     * Scope for date range
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%")
              ->orWhereHas('customer', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Generate order number
     */
    public static function generateOrderNumber()
    {
        $prefix = 'ORD-' . now()->format('Ym') . '-';
        $lastOrder = self::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate totals from items
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->items()->sum('total');
        $this->total_amount = $this->subtotal - $this->discount_amount + $this->delivery_fee;
        $this->save();
    }

    /**
     * Update payment status based on payments
     */
    public function updatePaymentStatus()
    {
        $totalPaid = $this->payments()
            ->where('status', 'completed')
            ->sum('amount');

        $this->amount_paid = $totalPaid;

        if ($totalPaid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($totalPaid >= $this->total_amount) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }

        $this->save();
    }

    /**
     * Get balance due
     */
    public function getBalanceDueAttribute()
    {
        return max(0, $this->total_amount - $this->amount_paid);
    }

    /**
     * Get order status badge styling
     */
    public function getOrderStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-gray-100 text-gray-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-yellow-100 text-yellow-800',
            'ready' => 'bg-purple-100 text-purple-800',
            'shipped' => 'bg-indigo-100 text-indigo-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->order_status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get payment status badge styling
     */
    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'unpaid' => 'bg-red-100 text-red-800',
            'partial' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'refunded' => 'bg-gray-100 text-gray-800',
        ];

        return $badges[$this->payment_status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->order_status, ['pending', 'confirmed']);
    }

    /**
     * Check if order is complete
     */
    public function isComplete()
    {
        return $this->order_status === 'delivered' && $this->payment_status === 'paid';
    }
}
