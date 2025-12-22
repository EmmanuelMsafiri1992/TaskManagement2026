<?php

namespace App\Services;

use App\Models\AdSenseReport;
use Google\Client;
use Google\Service\Adsense;
use Google\Service\Adsense\AdsenseReportsGenerateCall;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdSenseService
{
    protected $client;
    protected $service;
    protected $accountId;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Initialize Google Client with OAuth 2.0
     */
    protected function initializeClient()
    {
        try {
            // Get OAuth credentials from database or fallback to config
            $clientId = option('adsense_client_id') ?: config('services.adsense.client_id');
            $clientSecret = option('adsense_client_secret') ?: config('services.adsense.client_secret');
            $accessToken = option('adsense_access_token');
            $refreshToken = option('adsense_refresh_token');

            // Check if OAuth credentials are configured
            if (!$clientId || !$clientSecret) {
                $this->client = null;
                $this->service = null;
                $this->accountId = null;
                return;
            }

            $this->client = new Client();
            $this->client->setApplicationName(config('app.name'));
            $this->client->setScopes([Adsense::ADSENSE_READONLY]);
            $this->client->setClientId($clientId);
            $this->client->setClientSecret($clientSecret);
            $this->client->setRedirectUri(config('services.adsense.redirect_uri', url('auth/google/adsense/callback')));
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');

            // Set access token if available
            if ($accessToken) {
                $this->client->setAccessToken($accessToken);

                // Refresh token if expired
                if ($this->client->isAccessTokenExpired() && $refreshToken) {
                    $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    $newAccessToken = $this->client->getAccessToken();

                    // Save the new access token
                    option(['adsense_access_token' => $newAccessToken]);
                }
            }

            $this->service = new Adsense($this->client);
            $this->accountId = option('adsense_account_id');
        } catch (\Exception $e) {
            $this->client = null;
            $this->service = null;
            $this->accountId = null;
            Log::error('Failed to initialize AdSense client', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if AdSense is configured
     */
    public function isConfigured()
    {
        return $this->service !== null && $this->accountId !== null;
    }

    /**
     * Get OAuth authorization URL
     *
     * @return string
     */
    public function getAuthUrl()
    {
        // Get OAuth credentials from database or fallback to config
        $clientId = option('adsense_client_id') ?: config('services.adsense.client_id');
        $clientSecret = option('adsense_client_secret') ?: config('services.adsense.client_secret');

        if (!$clientId || !$clientSecret) {
            throw new \Exception('OAuth client credentials are not configured. Please add them in AdSense settings.');
        }

        $client = new Client();
        $client->setApplicationName(config('app.name'));
        $client->setScopes([Adsense::ADSENSE_READONLY]);
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri(config('services.adsense.redirect_uri', url('auth/google/adsense/callback')));
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client->createAuthUrl();
    }

    /**
     * Handle OAuth callback and store tokens
     *
     * @param string $code
     * @return bool
     */
    public function handleCallback($code)
    {
        try {
            // Get OAuth credentials from database or fallback to config
            $clientId = option('adsense_client_id') ?: config('services.adsense.client_id');
            $clientSecret = option('adsense_client_secret') ?: config('services.adsense.client_secret');

            $client = new Client();
            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->setRedirectUri(config('services.adsense.redirect_uri', url('auth/google/adsense/callback')));

            // Exchange authorization code for access token
            $accessToken = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($accessToken['error'])) {
                Log::error('OAuth callback error', ['error' => $accessToken]);
                return false;
            }

            // Store tokens in options
            option([
                'adsense_access_token' => json_encode($accessToken),
                'adsense_refresh_token' => $accessToken['refresh_token'] ?? null,
                'adsense_configured' => true,
            ]);

            // Reinitialize client with new tokens
            $this->initializeClient();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to handle OAuth callback', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Disconnect OAuth (revoke tokens)
     *
     * @return bool
     */
    public function disconnect()
    {
        try {
            if ($this->client) {
                $this->client->revokeToken();
            }

            option([
                'adsense_access_token' => null,
                'adsense_refresh_token' => null,
                'adsense_configured' => false,
                'adsense_account_id' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to disconnect AdSense', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Fetch reports from AdSense API
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function fetchReports($startDate, $endDate)
    {
        if (!$this->isConfigured()) {
            throw new \Exception('AdSense is not configured. Please configure AdSense credentials in settings.');
        }

        try {
            $cacheKey = "adsense_reports_{$startDate}_{$endDate}";

            return Cache::remember($cacheKey, now()->addHours(6), function () use ($startDate, $endDate) {
                Log::info('Fetching AdSense reports', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                // Build the report request
                $startCarbon = Carbon::parse($startDate);
                $endCarbon = Carbon::parse($endDate);

                $optParams = [
                    'dimensions' => ['DOMAIN_NAME', 'COUNTRY_CODE', 'COUNTRY_NAME'],
                    'metrics' => [
                        'IMPRESSIONS',
                        'CLICKS',
                        'PAGE_VIEWS',
                        'COST_PER_CLICK',
                        'PAGE_VIEWS_RPM',
                        'PAGE_VIEWS_CTR',
                        'ESTIMATED_EARNINGS',
                    ],
                    'dateRange' => 'CUSTOM',
                    'startDate.year' => $startCarbon->year,
                    'startDate.month' => $startCarbon->month,
                    'startDate.day' => $startCarbon->day,
                    'endDate.year' => $endCarbon->year,
                    'endDate.month' => $endCarbon->month,
                    'endDate.day' => $endCarbon->day,
                    'orderBy' => ['-IMPRESSIONS'], // Order by impressions descending
                ];

                $accountName = 'accounts/' . $this->accountId;
                $report = $this->service->accounts_reports->generate($accountName, $optParams);

                return $this->processReport($report, $startDate, $endDate);
            });
        } catch (\Exception $e) {
            Log::error('Failed to fetch AdSense reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Process and save report data
     *
     * @param mixed $report
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    protected function processReport($report, $startDate, $endDate)
    {
        $processedData = [];
        $headers = $report->getHeaders();
        $rows = $report->getRows();

        if (!$rows) {
            return $processedData;
        }

        foreach ($rows as $row) {
            $cells = $row->getCells();

            if (count($cells) < 10) {
                continue;
            }

            // Extract data from cells (now includes DOMAIN_NAME as first dimension)
            $domain = $cells[0]->getValue() ?? null;
            $countryCode = $cells[1]->getValue() ?? 'XX';
            $countryName = $cells[2]->getValue() ?? 'Unknown';
            $impressions = (int) ($cells[3]->getValue() ?? 0);
            $clicks = (int) ($cells[4]->getValue() ?? 0);
            $pageViews = (int) ($cells[5]->getValue() ?? 0);
            $cpc = (float) ($cells[6]->getValue() ?? 0);
            $pageRpm = (float) ($cells[7]->getValue() ?? 0);
            $pageCtr = (float) ($cells[8]->getValue() ?? 0);
            $earnings = (float) ($cells[9]->getValue() ?? 0);

            // Save to database
            $reportData = [
                'report_date' => $startDate,
                'country_code' => $countryCode,
                'country_name' => $countryName,
                'domain' => $domain,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'page_views' => $pageViews,
                'cpc' => $cpc,
                'page_rpm' => $pageRpm,
                'page_ctr' => $pageCtr,
                'earnings' => $earnings,
            ];

            AdSenseReport::updateOrCreate(
                [
                    'report_date' => $startDate,
                    'country_code' => $countryCode,
                    'domain' => $domain,
                ],
                $reportData
            );

            $processedData[] = $reportData;
        }

        Log::info('AdSense reports processed', [
            'count' => count($processedData),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $processedData;
    }

    /**
     * Get available AdSense accounts
     *
     * @return array
     */
    public function getAccounts()
    {
        if (!$this->isConfigured()) {
            throw new \Exception('AdSense is not configured. Please configure AdSense credentials in settings.');
        }

        try {
            $accounts = $this->service->accounts->listAccounts();

            return collect($accounts->getAccounts())->map(function ($account) {
                return [
                    'id' => $account->getName(),
                    'display_name' => $account->getDisplayName(),
                    'state' => $account->getState(),
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to fetch AdSense accounts', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Test AdSense API connection
     *
     * @return bool
     */
    public function testConnection()
    {
        try {
            $this->getAccounts();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sync yesterday's data
     *
     * @return array
     */
    public function syncYesterdayData()
    {
        $yesterday = Carbon::yesterday()->toDateString();
        return $this->fetchReports($yesterday, $yesterday);
    }

    /**
     * Sync data for date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function syncDateRange($startDate, $endDate)
    {
        return $this->fetchReports($startDate, $endDate);
    }
}
