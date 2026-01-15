<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'page_url',
        'page_title',
        'last_activity_at',
        'is_active',
        'session_id',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns this activity log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active logs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get logs for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent logs (within specified minutes).
     */
    public function scopeRecent($query, int $minutes = 5)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope to get stale logs (inactive for specified minutes).
     */
    public function scopeStale($query, int $minutes = 5)
    {
        return $query->where('last_activity_at', '<', now()->subMinutes($minutes));
    }
}
