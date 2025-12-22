<?php

namespace App\Services;

use App\Models\ShortenedUrl;
use Illuminate\Support\Facades\Log;

class UrlShortenerService
{
    /**
     * Shorten a URL.
     *
     * @param  string  $originalUrl
     * @param  int|null  $userId
     * @param  int|null  $expiresInDays
     * @return ShortenedUrl|null
     */
    public function shorten($originalUrl, $userId = null, $expiresInDays = null)
    {
        try {
            // Check if URL already exists
            $existing = ShortenedUrl::where('original_url', $originalUrl)
                ->active()
                ->first();

            if ($existing) {
                return $existing;
            }

            // Create new shortened URL
            $shortCode = ShortenedUrl::generateShortCode();
            $expiresAt = $expiresInDays ? now()->addDays($expiresInDays) : null;

            $shortenedUrl = ShortenedUrl::create([
                'original_url' => $originalUrl,
                'short_code' => $shortCode,
                'created_by' => $userId,
                'expires_at' => $expiresAt,
            ]);

            Log::info('URL shortened', [
                'original' => $originalUrl,
                'short_code' => $shortCode,
                'user_id' => $userId,
            ]);

            return $shortenedUrl;
        } catch (\Exception $e) {
            Log::error('Failed to shorten URL', [
                'url' => $originalUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get original URL from short code.
     *
     * @param  string  $shortCode
     * @return string|null
     */
    public function resolve($shortCode)
    {
        $shortenedUrl = ShortenedUrl::where('short_code', $shortCode)
            ->active()
            ->first();

        if (!$shortenedUrl) {
            return null;
        }

        // Increment click counter
        $shortenedUrl->incrementClicks();

        return $shortenedUrl->original_url;
    }

    /**
     * Get shortened URL statistics.
     *
     * @param  string  $shortCode
     * @return array|null
     */
    public function getStats($shortCode)
    {
        $shortenedUrl = ShortenedUrl::where('short_code', $shortCode)->first();

        if (!$shortenedUrl) {
            return null;
        }

        return [
            'original_url' => $shortenedUrl->original_url,
            'short_url' => $shortenedUrl->short_url,
            'clicks' => $shortenedUrl->clicks,
            'created_at' => $shortenedUrl->created_at,
            'expires_at' => $shortenedUrl->expires_at,
            'is_expired' => $shortenedUrl->isExpired(),
        ];
    }

    /**
     * Delete expired URLs.
     *
     * @return int Number of deleted URLs
     */
    public function cleanupExpired()
    {
        return ShortenedUrl::where('expires_at', '<=', now())->delete();
    }
}
