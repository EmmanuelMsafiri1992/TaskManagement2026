<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivitySession extends Model
{
    use HasFactory;

    protected $table = 'user_activity_sessions';

    protected $fillable = [
        'user_id',
        'session_id',
        'started_at',
        'last_heartbeat_at',
        'ended_at',
        'graceful_logout',
        'last_page_url',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'ended_at' => 'datetime',
        'graceful_logout' => 'boolean',
    ];

    /**
     * Get the user that owns this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active sessions (not ended).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope to get sessions for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get sessions that ended ungracefully (possible power outage).
     */
    public function scopeUngraceful($query)
    {
        return $query->whereNotNull('ended_at')
            ->where('graceful_logout', false);
    }

    /**
     * End this session gracefully.
     */
    public function endGracefully(): bool
    {
        return $this->update([
            'ended_at' => now(),
            'graceful_logout' => true,
        ]);
    }

    /**
     * End this session ungracefully (detected as stale).
     */
    public function endUngracefully(): bool
    {
        return $this->update([
            'ended_at' => $this->last_heartbeat_at,
            'graceful_logout' => false,
        ]);
    }

    /**
     * Update the heartbeat.
     */
    public function heartbeat(?string $pageUrl = null): bool
    {
        $data = ['last_heartbeat_at' => now()];

        if ($pageUrl) {
            $data['last_page_url'] = $pageUrl;
        }

        return $this->update($data);
    }

    /**
     * Calculate session duration in minutes.
     */
    public function getDurationMinutesAttribute(): int
    {
        $end = $this->ended_at ?? now();
        return (int) $this->started_at->diffInMinutes($end);
    }
}
