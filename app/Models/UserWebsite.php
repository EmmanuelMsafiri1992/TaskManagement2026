<?php

namespace App\Models;

use App\Models\V11\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWebsite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_country_id',
        'company_id',
        'website_url',
        'company_name',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the user that owns this website assignment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user country assignment
     */
    public function userCountry()
    {
        return $this->belongsTo(UserCountry::class);
    }

    /**
     * Get the company details from v11 database
     */
    public function companyDetails()
    {
        return Company::find($this->company_id);
    }
}
