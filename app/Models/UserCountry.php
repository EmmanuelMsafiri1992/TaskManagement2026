<?php

namespace App\Models;

use App\Models\V11\Country as V11Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCountry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'country_code',
        'country_name',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the user that owns this country assignment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country details from v11 database
     */
    public function countryDetails()
    {
        return V11Country::where('code', $this->country_code)->first();
    }

    /**
     * Get the user who assigned this country
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the websites assigned to this user for this country
     */
    public function websites()
    {
        return $this->hasMany(UserWebsite::class, 'user_country_id');
    }
}
