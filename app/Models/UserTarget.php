<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTarget extends Model
{
    protected $table = 'user_targets';

    protected $fillable = [
        'user_id',
        'daily_impressions_target',
        'daily_page_views_target',
        'daily_clicks_target',
        'min_cpc_target',
        'min_rpm_target',
        'daily_earnings_target',
    ];

    protected $casts = [
        'daily_impressions_target' => 'integer',
        'daily_page_views_target' => 'integer',
        'daily_clicks_target' => 'integer',
        'min_cpc_target' => 'decimal:2',
        'min_rpm_target' => 'decimal:2',
        'daily_earnings_target' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
