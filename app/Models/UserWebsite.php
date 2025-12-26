<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWebsite extends Model
{
    protected $table = 'user_websites';

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userCountry(): BelongsTo
    {
        return $this->belongsTo(UserCountry::class);
    }
}
