<?php

namespace App\Models\V11;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
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
    protected $table = 'countries';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'admin_field_active' => 'boolean',
    ];

    /**
     * Get the country name in English
     *
     * @return string
     */
    public function getNameAttribute($value)
    {
        // If value is already decoded (from cast), return it
        if (is_array($value)) {
            return $value['en'] ?? (is_string($value) ? $value : '');
        }

        // Otherwise decode it
        $names = json_decode($value, true);

        if (is_array($names)) {
            return $names['en'] ?? $value;
        }

        return $value;
    }

    /**
     * Get all translations of the country name
     *
     * @return array
     */
    public function getAllNamesAttribute()
    {
        $nameValue = $this->attributes['name'] ?? '';

        if (is_array($nameValue)) {
            return $nameValue;
        }

        $decoded = json_decode($nameValue, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get the companies in this country
     */
    public function companies()
    {
        return $this->hasMany(Company::class, 'country_code', 'code');
    }

    /**
     * Scope to get only active countries
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
