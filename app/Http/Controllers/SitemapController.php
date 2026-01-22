<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    /**
     * Generate and return the XML sitemap.
     *
     * @return Response
     */
    public function index(): Response
    {
        $urls = $this->getSitemapUrls();

        $content = $this->generateSitemapXml($urls);

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Get all URLs that should be included in the sitemap.
     *
     * @return array
     */
    protected function getSitemapUrls(): array
    {
        $urls = [];
        $baseUrl = config('app.url');

        // Homepage - highest priority
        $urls[] = [
            'loc' => $baseUrl,
            'lastmod' => now()->toDateString(),
            'changefreq' => config('seo.sitemap.homepage_changefreq', 'daily'),
            'priority' => config('seo.sitemap.homepage_priority', '1.0'),
        ];

        // Add public routes from the router
        $publicRoutes = $this->getPublicRoutes();

        foreach ($publicRoutes as $route) {
            $urls[] = [
                'loc' => $baseUrl . '/' . ltrim($route, '/'),
                'lastmod' => now()->toDateString(),
                'changefreq' => config('seo.sitemap.default_changefreq', 'weekly'),
                'priority' => config('seo.sitemap.default_priority', '0.8'),
            ];
        }

        return $urls;
    }

    /**
     * Get public routes that should be included in the sitemap.
     * Excludes auth routes, admin routes, and API routes.
     *
     * @return array
     */
    protected function getPublicRoutes(): array
    {
        $routes = [];
        $excludePatterns = [
            'login',
            'register',
            'logout',
            'password',
            'forgot-password',
            'reset-password',
            'verify-email',
            'email/verification',
            'confirm-password',
            'admin',
            'dashboard',
            'api',
            'sanctum',
            '_ignition',
            'sitemap.xml',
        ];

        $routeCollection = Route::getRoutes();

        foreach ($routeCollection as $route) {
            // Only include GET routes
            if (!in_array('GET', $route->methods())) {
                continue;
            }

            $uri = $route->uri();

            // Skip routes with parameters (dynamic routes)
            if (str_contains($uri, '{')) {
                continue;
            }

            // Skip excluded patterns
            $shouldExclude = false;
            foreach ($excludePatterns as $pattern) {
                if (str_starts_with($uri, $pattern) || str_contains($uri, $pattern)) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                continue;
            }

            // Skip if route requires authentication middleware
            $middleware = $route->middleware();
            if (is_array($middleware) && (in_array('auth', $middleware) || in_array('auth:sanctum', $middleware))) {
                continue;
            }

            // Skip homepage (already added)
            if ($uri === '/' || $uri === '') {
                continue;
            }

            $routes[] = $uri;
        }

        return array_unique($routes);
    }

    /**
     * Generate the XML sitemap content.
     *
     * @param array $urls
     * @return string
     */
    protected function generateSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
