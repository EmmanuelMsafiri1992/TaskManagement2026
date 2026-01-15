<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'user_id',
        'client_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'quotation_date',
        'valid_until',
        'currency',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'template',
        'notes',
        'terms',
        'status',
        'color',
        'logo',
        'business_name',
        'business_address',
        'business_phone',
        'business_email',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('quotation_number', 'like', "%{$search}%")
              ->orWhere('customer_name', 'like', "%{$search}%")
              ->orWhere('customer_email', 'like', "%{$search}%");
        });
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Generate a unique quotation number
     */
    public static function generateQuotationNumber()
    {
        $prefix = 'QT-';
        $year = date('Y');
        $month = date('m');

        $lastQuotation = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastQuotation) {
            $lastNumber = intval(substr($lastQuotation->quotation_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . $month . '-' . $newNumber;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'sent' => 'bg-blue-100 text-blue-800',
            'accepted' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'expired' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
