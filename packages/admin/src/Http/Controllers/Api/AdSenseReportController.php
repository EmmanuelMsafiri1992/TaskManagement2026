<?php

namespace Admin\Http\Controllers\Api;

use App\Models\AdSenseReport;
use App\Services\AdSenseService;
use App\Services\GoogleAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdSenseReportController
{
    protected $adSenseService;
    protected $analyticsService;

    public function __construct(AdSenseService $adSenseService, GoogleAnalyticsService $analyticsService)
    {
        $this->adSenseService = $adSenseService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get AdSense reports for date range
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date', Carbon::yesterday()->toDateString());
        $endDate = $request->input('end_date', Carbon::yesterday()->toDateString());

        $reports = AdSenseReport::getByDateRange($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $reports,
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_records' => $reports->count(),
            ],
        ]);
    }

    /**
     * Get reports grouped by country
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function byCountry(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $startDate = $request->input('start_date', Carbon::yesterday()->toDateString());
        $endDate = $request->input('end_date', Carbon::yesterday()->toDateString());
        $search = $request->input('search', '');
        $perPage = $request->input('per_page', 10);

        $query = AdSenseReport::selectRaw('
                country_code,
                country_name,
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(page_views) as total_page_views,
                AVG(cpc) as avg_cpc,
                AVG(page_rpm) as avg_page_rpm,
                AVG(page_ctr) as avg_page_ctr,
                SUM(earnings) as total_earnings
            ')
            ->whereBetween('report_date', [$startDate, $endDate])
            ->groupBy('country_code', 'country_name')
            ->orderBy('total_impressions', 'desc');

        // Filter by user's assigned countries
        $user = auth()->user();
        if ($user) {
            $userCountries = $user->countries()->pluck('country_code')->toArray();
            if (!empty($userCountries)) {
                $query->whereIn('country_code', $userCountries);
            }
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('country_name', 'like', "%{$search}%")
                  ->orWhere('country_code', 'like', "%{$search}%");
            });
        }

        $reports = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reports->items(),
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_countries' => $reports->total(),
                'current_page' => $reports->currentPage(),
                'per_page' => $reports->perPage(),
                'last_page' => $reports->lastPage(),
                'from' => $reports->firstItem(),
                'to' => $reports->lastItem(),
            ],
        ]);
    }

    /**
     * Sync data from AdSense API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request)
    {
        try {
            // Check if AdSense is configured
            if (!$this->adSenseService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AdSense is not configured. Please configure your AdSense credentials in Settings.',
                    'configured' => false,
                ], 400);
            }

            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $startDate = $request->input('start_date', Carbon::yesterday()->toDateString());
            $endDate = $request->input('end_date', Carbon::yesterday()->toDateString());

            Log::info('Manual AdSense sync requested', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'user_id' => auth()->id(),
            ]);

            $reports = $this->adSenseService->syncDateRange($startDate, $endDate);

            return response()->json([
                'success' => true,
                'message' => 'AdSense data synced successfully',
                'data' => $reports,
                'meta' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'records_synced' => count($reports),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AdSense sync failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync AdSense data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get summary statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date', Carbon::yesterday()->toDateString());
        $endDate = $request->input('end_date', Carbon::yesterday()->toDateString());

        $query = AdSenseReport::selectRaw('
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(page_views) as total_page_views,
                AVG(cpc) as avg_cpc,
                AVG(page_rpm) as avg_page_rpm,
                AVG(page_ctr) as avg_page_ctr,
                SUM(earnings) as total_earnings,
                COUNT(DISTINCT country_code) as total_countries
            ')
            ->whereBetween('report_date', [$startDate, $endDate]);

        // Filter by user's assigned countries
        $user = auth()->user();
        if ($user) {
            $userCountries = $user->countries()->pluck('country_code')->toArray();
            if (!empty($userCountries)) {
                $query->whereIn('country_code', $userCountries);
            }
        }

        $summary = $query->first();

        return response()->json([
            'success' => true,
            'data' => $summary,
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Test AdSense API connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection()
    {
        try {
            // Check if AdSense is configured
            if (!$this->adSenseService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AdSense is not configured. Please configure your AdSense credentials in Settings.',
                    'configured' => false,
                ], 400);
            }

            $connected = $this->adSenseService->testConnection();

            if ($connected) {
                $accounts = $this->adSenseService->getAccounts();

                return response()->json([
                    'success' => true,
                    'message' => 'AdSense API connection successful',
                    'accounts' => $accounts,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to AdSense API',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AdSense API connection error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get latest report date
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function latestDate()
    {
        $latestDate = AdSenseReport::getLatestReportDate();

        return response()->json([
            'success' => true,
            'data' => [
                'latest_report_date' => $latestDate,
            ],
        ]);
    }

    /**
     * Get reports grouped by website
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function byWebsite(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date', Carbon::yesterday()->toDateString());
        $endDate = $request->input('end_date', Carbon::yesterday()->toDateString());

        $query = AdSenseReport::selectRaw('
                domain,
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(page_views) as total_page_views,
                AVG(cpc) as avg_cpc,
                AVG(page_rpm) as avg_page_rpm,
                AVG(page_ctr) as avg_page_ctr,
                SUM(earnings) as total_earnings,
                COUNT(DISTINCT country_code) as countries_count
            ')
            ->whereBetween('report_date', [$startDate, $endDate])
            ->whereNotNull('domain')
            ->groupBy('domain')
            ->orderBy('total_earnings', 'desc');

        // Filter by user's assigned countries
        $user = auth()->user();
        if ($user) {
            $userCountries = $user->countries()->pluck('country_code')->toArray();
            if (!empty($userCountries)) {
                $query->whereIn('country_code', $userCountries);
            }

            // Filter by user's assigned websites from UserCountry assignments
            $userWebsiteUrls = \App\Models\UserWebsite::where('user_id', $user->id)
                ->pluck('website_url')
                ->map(function ($url) {
                    // Extract domain from URL
                    $parsed = parse_url($url);
                    return $parsed['host'] ?? $url;
                })
                ->filter()
                ->toArray();

            if (!empty($userWebsiteUrls)) {
                $query->where(function ($q) use ($userWebsiteUrls) {
                    foreach ($userWebsiteUrls as $domain) {
                        $q->orWhere('domain', 'like', "%{$domain}%");
                    }
                });
            }
        }

        $websites = $query->get();

        return response()->json([
            'success' => true,
            'data' => $websites,
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_websites' => $websites->count(),
            ],
        ]);
    }

    /**
     * Get user's assigned countries and websites
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userAssignments()
    {
        $user = auth()->user();

        $countries = $user->countries()
            ->with('websites')
            ->select('id', 'country_code', 'country_name', 'assigned_at')
            ->get()
            ->map(function ($country) {
                return [
                    'id' => $country->id,
                    'country_code' => $country->country_code,
                    'country_name' => $country->country_name,
                    'assigned_at' => $country->assigned_at,
                    'websites' => $country->websites->map(function ($website) {
                        return [
                            'id' => $website->id,
                            'company_id' => $website->company_id,
                            'company_name' => $website->company_name,
                            'website_url' => $website->website_url,
                        ];
                    }),
                ];
            });

        // Get user's performance targets
        $target = $user->target;

        return response()->json([
            'success' => true,
            'data' => [
                'countries' => $countries,
                'has_assignments' => $countries->isNotEmpty(),
                'targets' => $target,
            ],
        ]);
    }

    /**
     * Get daily trend data for charts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailyTrend(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date', Carbon::yesterday()->subDays(6)->toDateString());
        $endDate = $request->input('end_date', Carbon::yesterday()->toDateString());

        $query = AdSenseReport::selectRaw('
                report_date,
                SUM(impressions) as total_impressions,
                SUM(clicks) as total_clicks,
                SUM(page_views) as total_page_views,
                SUM(earnings) as total_earnings
            ')
            ->whereBetween('report_date', [$startDate, $endDate])
            ->groupBy('report_date')
            ->orderBy('report_date', 'asc');

        // Filter by user's assigned countries
        $user = auth()->user();
        if ($user) {
            $userCountries = $user->countries()->pluck('country_code')->toArray();
            if (!empty($userCountries)) {
                $query->whereIn('country_code', $userCountries);
            }
        }

        $dailyData = $query->get();

        return response()->json([
            'success' => true,
            'data' => $dailyData,
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Get traffic sources from Google Analytics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trafficSources(Request $request)
    {
        try {
            // Check if Analytics is configured
            if (!$this->analyticsService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Analytics is not configured. Please add GA4 Property ID in settings.',
                    'configured' => false,
                ], 400);
            }

            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'domain' => 'nullable|string',
            ]);

            $startDate = $request->input('start_date', Carbon::yesterday()->toDateString());
            $endDate = $request->input('end_date', Carbon::yesterday()->toDateString());
            $domain = $request->input('domain');

            if ($domain) {
                $sources = $this->analyticsService->getTrafficSources($startDate, $endDate, $domain);
            } else {
                $sources = $this->analyticsService->getTrafficSourcesByDomain($startDate, $endDate);
            }

            return response()->json([
                'success' => true,
                'data' => $sources,
                'meta' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'domain' => $domain,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch traffic sources', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch traffic sources: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test Analytics API connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testAnalyticsConnection()
    {
        try {
            // Check if Analytics is configured
            if (!$this->analyticsService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Analytics is not configured. Please add GA4 Property ID in settings.',
                    'configured' => false,
                ], 400);
            }

            $connected = $this->analyticsService->testConnection();

            if ($connected) {
                return response()->json([
                    'success' => true,
                    'message' => 'Google Analytics API connection successful',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to Google Analytics API',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google Analytics API connection error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
