<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'secondary_phone',
        'company_name',
        'business_type',
        'address',
        'city',
        'country',
        'website',
        'notes',
        'status',
    ];

    /**
     * Relationships
     */
    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Scopes
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeProspect($query)
    {
        return $query->where('status', 'prospect');
    }
}
