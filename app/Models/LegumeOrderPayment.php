<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegumeOrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_reference',
        'legume_order_id',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'phone_number',
        'payment_date',
        'status',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_reference) {
                $payment->payment_reference = self::generatePaymentReference();
            }
        });

        static::saved(function ($payment) {
            // Update order payment status when payment is saved
            $payment->order->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            // Update order payment status when payment is deleted
            $payment->order->updatePaymentStatus();
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
     * Get the user who recorded this payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by payment method
     */
    public function scopePaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope for date range
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('payment_reference', 'like', "%{$search}%")
              ->orWhere('transaction_id', 'like', "%{$search}%")
              ->orWhere('phone_number', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    /**
     * Generate payment reference
     */
    public static function generatePaymentReference()
    {
        $prefix = 'PAY-' . now()->format('Ym') . '-';
        $lastPayment = self::where('payment_reference', 'like', $prefix . '%')
            ->orderBy('payment_reference', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_reference, -4);
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
            'failed' => 'bg-red-100 text-red-800',
            'reversed' => 'bg-gray-100 text-gray-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'airtel_money' => 'Airtel Money',
            'tnm_mpamba' => 'TNM Mpamba',
            'other' => 'Other',
        ];

        return $labels[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Check if this is a mobile money payment
     */
    public function isMobileMoney()
    {
        return in_array($this->payment_method, ['airtel_money', 'tnm_mpamba']);
    }
}
