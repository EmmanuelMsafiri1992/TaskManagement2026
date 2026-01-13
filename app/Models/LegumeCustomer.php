<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegumeCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'secondary_phone',
        'email',
        'address',
        'city',
        'district',
        'delivery_notes',
        'total_purchases',
        'status',
    ];

    protected $casts = [
        'total_purchases' => 'decimal:2',
    ];

    /**
     * Get all orders for this customer
     */
    public function orders()
    {
        return $this->hasMany(LegumeOrder::class);
    }

    /**
     * Scope for active customers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive customers
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by district
     */
    public function scopeDistrict($query, $district)
    {
        return $query->where('district', $district);
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%")
              ->orWhere('district', 'like', "%{$search}%");
        });
    }

    /**
     * Get order count
     */
    public function getOrderCountAttribute()
    {
        return $this->orders()->count();
    }

    /**
     * Update total purchases amount
     */
    public function updateTotalPurchases()
    {
        $this->total_purchases = $this->orders()
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $this->save();
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->district,
        ]);
        return implode(', ', $parts);
    }
}
