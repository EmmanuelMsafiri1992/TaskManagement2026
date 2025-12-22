<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * ShortenedUrl Model - Custom URL shortening service
 *
 * @property int $id
 * @property string $original_url
 * @property string $short_code
 * @property int $clicks
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ShortenedUrl extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'original_url',
        'short_code',
        'clicks',
        'created_by',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'clicks' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who created this shortened URL.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate a unique short code.
     *
     * @param  int  $length
     * @return string
     */
    public static function generateShortCode($length = 6)
    {
        do {
            $code = Str::random($length);
        } while (self::where('short_code', $code)->exists());

        return $code;
    }

    /**
     * Increment click counter.
     *
     * @return bool
     */
    public function incrementClicks()
    {
        return $this->increment('clicks');
    }

    /**
     * Check if URL has expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope to get non-expired URLs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Get the full shortened URL.
     *
     * @return string
     */
    public function getShortUrlAttribute()
    {
        return url('/s/' . $this->short_code);
    }
}
