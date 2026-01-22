<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'trading_name',
        'registration_number',
        'tax_number',
        'email',
        'phone',
        'secondary_phone',
        'fax',
        'website',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'logo',
        'industry',
        'description',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'bank_swift_code',
        'status',
        'notes',
    ];

    /**
     * Get the projects for the company.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the quotations for the company.
     */
    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Scope for searching companies.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('trading_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('registration_number', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive companies.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get full address attribute.
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);
        return implode(', ', $parts);
    }
}
