<?php

namespace App\Http\Controllers;

use App\Services\UrlShortenerService;
use Illuminate\Http\Request;

class UrlShortenerController extends Controller
{
    /**
     * @var UrlShortenerService
     */
    protected $urlShortener;

    /**
     * Constructor.
     *
     * @param  UrlShortenerService  $urlShortener
     */
    public function __construct(UrlShortenerService $urlShortener)
    {
        $this->urlShortener = $urlShortener;
    }

    /**
     * Redirect to original URL.
     *
     * @param  string  $shortCode
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($shortCode)
    {
        $originalUrl = $this->urlShortener->resolve($shortCode);

        if (!$originalUrl) {
            abort(404, 'Short URL not found or expired');
        }

        return redirect($originalUrl);
    }

    /**
     * Get statistics for a shortened URL.
     *
     * @param  string  $shortCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats($shortCode)
    {
        $stats = $this->urlShortener->getStats($shortCode);

        if (!$stats) {
            return response()->json(['error' => 'Short URL not found'], 404);
        }

        return response()->json($stats);
    }
}
