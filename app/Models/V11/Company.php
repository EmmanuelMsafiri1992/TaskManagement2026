<?php

namespace App\Models\V11;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'v11';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * Get the country this company belongs to
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /**
     * Scope to filter by country
     */
    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Scope to get companies with websites
     */
    public function scopeWithWebsite($query)
    {
        return $query->whereNotNull('website')->where('website', '!=', '');
    }
}
