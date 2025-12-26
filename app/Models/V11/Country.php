<?php

namespace App\Models\V11;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $connection = 'v11';
    protected $table = 'countries';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAllNamesAttribute()
    {
        return $this->name;
    }
}
