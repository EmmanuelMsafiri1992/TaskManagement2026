<?php

namespace App\Models\V11;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'v11';
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'website',
        'email',
        'phone',
        'country_code',
    ];

    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeWithWebsite($query)
    {
        return $query->whereNotNull('website')->where('website', '!=', '');
    }
}
