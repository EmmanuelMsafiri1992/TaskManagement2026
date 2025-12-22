<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTarget extends Model
{
    use HasFactory;

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
        'min_cpc_target' => 'decimal:4',
        'min_rpm_target' => 'decimal:4',
        'daily_earnings_target' => 'decimal:2',
    ];

    /**
     * Get the user that owns this target
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
