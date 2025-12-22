<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleAnalyticsService
{
    protected $client;
    protected $propertyId;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Initialize Google Analytics Data API Client
     */
    protected function initializeClient()
    {
        try {
            $credentialsPath = storage_path('app/credentials/google-analytics.json');

            if (!file_exists($credentialsPath)) {
                Log::warning('Google Analytics credentials file not found', [
                    'path' => $credentialsPath
                ]);
                $this->client = null;
                return;
            }

            // Set the credentials environment variable
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);

            $this->client = new BetaAnalyticsDataClient();
            $this->propertyId = option('ga4_property_id');
        } catch (\Exception $e) {
            $this->client = null;
            Log::error('Failed to initialize Google Analytics client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if Analytics is configured
     */
    public function isConfigured()
    {
        return $this->client !== null && $this->propertyId !== null;
    }

    /**
     * Get traffic sources for a date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param string|null $domain Optional domain filter
     * @return array
     */
    public function getTrafficSources($startDate, $endDate, $domain = null)
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Google Analytics is not configured. Please add GA4 Property ID in settings.');
        }

        try {
            $request = (new RunReportRequest())
                ->setProperty('properties/' . $this->propertyId)
                ->setDateRanges([
                    (new DateRange())
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                ])
                ->setDimensions([
                    (new Dimension())->setName('sessionSource'),
                    (new Dimension())->setName('sessionMedium'),
                    (new Dimension())->setName('hostName'),
                ])
                ->setMetrics([
                    (new Metric())->setName('sessions'),
                    (new Metric())->setName('totalUsers'),
                    (new Metric())->setName('newUsers'),
                    (new Metric())->setName('screenPageViews'),
                ])
                ->setLimit(100);

            $response = $this->client->runReport($request);

            return $this->processTrafficSourcesReport($response, $domain);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Google Analytics traffic sources', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Process traffic sources report
     *
     * @param mixed $response
     * @param string|null $domain
     * @return array
     */
    protected function processTrafficSourcesReport($response, $domain = null)
    {
        $sources = [];

        foreach ($response->getRows() as $row) {
            $dimensions = $row->getDimensionValues();
            $metrics = $row->getMetricValues();

            $source = $dimensions[0]->getValue() ?? '(direct)';
            $medium = $dimensions[1]->getValue() ?? '(none)';
            $hostname = $dimensions[2]->getValue() ?? '';

            // Filter by domain if specified
            if ($domain && $hostname !== $domain) {
                continue;
            }

            // Combine source and medium for better categorization
            $sourceLabel = $this->categorizeSource($source, $medium);

            $sessions = (int) ($metrics[0]->getValue() ?? 0);
            $users = (int) ($metrics[1]->getValue() ?? 0);
            $newUsers = (int) ($metrics[2]->getValue() ?? 0);
            $pageViews = (int) ($metrics[3]->getValue() ?? 0);

            $sources[] = [
                'source' => $sourceLabel,
                'original_source' => $source,
                'medium' => $medium,
                'hostname' => $hostname,
                'sessions' => $sessions,
                'users' => $users,
                'new_users' => $newUsers,
                'page_views' => $pageViews,
            ];
        }

        return $sources;
    }

    /**
     * Categorize and format traffic sources
     *
     * @param string $source
     * @param string $medium
     * @return string
     */
    protected function categorizeSource($source, $medium)
    {
        // Direct traffic
        if ($source === '(direct)' || $medium === '(none)') {
            return 'Direct';
        }

        // Organic search
        if (stripos($medium, 'organic') !== false) {
            if (stripos($source, 'google') !== false) {
                return 'Google (Organic)';
            } elseif (stripos($source, 'bing') !== false) {
                return 'Bing (Organic)';
            } elseif (stripos($source, 'yahoo') !== false) {
                return 'Yahoo (Organic)';
            }
            return ucfirst($source) . ' (Organic)';
        }

        // Paid search
        if (stripos($medium, 'cpc') !== false || stripos($medium, 'ppc') !== false) {
            return ucfirst($source) . ' (Paid)';
        }

        // Social media
        if (stripos($medium, 'social') !== false ||
            stripos($source, 'facebook') !== false ||
            stripos($source, 'twitter') !== false ||
            stripos($source, 'linkedin') !== false ||
            stripos($source, 'instagram') !== false) {
            return ucfirst($source) . ' (Social)';
        }

        // Referral
        if (stripos($medium, 'referral') !== false) {
            return ucfirst($source) . ' (Referral)';
        }

        // Email
        if (stripos($medium, 'email') !== false) {
            return 'Email';
        }

        // Default
        return ucfirst($source);
    }

    /**
     * Get traffic sources grouped by website domain
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getTrafficSourcesByDomain($startDate, $endDate)
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Google Analytics is not configured. Please add GA4 Property ID in settings.');
        }

        try {
            $allSources = $this->getTrafficSources($startDate, $endDate);

            // Group by domain
            $groupedByDomain = [];

            foreach ($allSources as $source) {
                $domain = $source['hostname'];

                if (!isset($groupedByDomain[$domain])) {
                    $groupedByDomain[$domain] = [];
                }

                $groupedByDomain[$domain][] = $source;
            }

            // Sort each domain's sources by sessions
            foreach ($groupedByDomain as $domain => &$sources) {
                usort($sources, function($a, $b) {
                    return $b['sessions'] - $a['sessions'];
                });
            }

            return $groupedByDomain;
        } catch (\Exception $e) {
            Log::error('Failed to fetch traffic sources by domain', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Test Analytics API connection
     *
     * @return bool
     */
    public function testConnection()
    {
        try {
            if (!$this->isConfigured()) {
                return false;
            }

            // Try to fetch data for yesterday
            $yesterday = Carbon::yesterday()->toDateString();
            $this->getTrafficSources($yesterday, $yesterday);

            return true;
        } catch (\Exception $e) {
            Log::error('Analytics connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
