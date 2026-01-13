<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'secondary_phone',
        'email',
        'national_id',
        'address',
        'district',
        'village',
        'notes',
        'status',
        'total_supplied',
    ];

    protected $casts = [
        'total_supplied' => 'decimal:2',
    ];

    /**
     * Get all purchases from this supplier
     */
    public function purchases()
    {
        return $this->hasMany(LegumePurchase::class);
    }

    /**
     * Scope for active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive suppliers
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
              ->orWhere('national_id', 'like', "%{$search}%")
              ->orWhere('district', 'like', "%{$search}%")
              ->orWhere('village', 'like', "%{$search}%");
        });
    }

    /**
     * Get purchase count
     */
    public function getPurchaseCountAttribute()
    {
        return $this->purchases()->count();
    }

    /**
     * Update total supplied amount
     */
    public function updateTotalSupplied()
    {
        $this->total_supplied = $this->purchases()
            ->where('status', 'completed')
            ->sum('grand_total');
        $this->save();
    }
}
