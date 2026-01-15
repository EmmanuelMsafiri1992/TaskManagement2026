<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'processed_by',
        'amount',
        'payment_method',
        'reference_number',
        'transaction_id',
        'payment_date',
        'status',
        'month_for',
        'notes',
        'receipt_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->receipt_number) {
                $payment->receipt_number = 'RCP-' . date('Ymd') . '-' . str_pad(static::count() + 1, 5, '0', STR_PAD_LEFT);
            }
        });

        static::created(function ($payment) {
            if ($payment->status === 'completed') {
                $payment->serviceProvider->increment('total_paid', $payment->amount);
            }
        });

        static::updated(function ($payment) {
            if ($payment->isDirty('status')) {
                $originalStatus = $payment->getOriginal('status');
                $newStatus = $payment->status;

                // If status changed to completed, add to total_paid
                if ($originalStatus !== 'completed' && $newStatus === 'completed') {
                    $payment->serviceProvider->increment('total_paid', $payment->amount);
                }
                // If status changed from completed to something else, subtract from total_paid
                elseif ($originalStatus === 'completed' && $newStatus !== 'completed') {
                    $payment->serviceProvider->decrement('total_paid', $payment->amount);
                }
            }
        });
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getBalanceAfterAttribute()
    {
        return $this->serviceProvider->total_agreed_amount - $this->serviceProvider->total_paid;
    }

    public function getFormattedAmountAttribute()
    {
        return 'MK ' . number_format($this->amount, 2);
    }
}
