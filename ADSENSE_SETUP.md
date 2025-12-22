# Google AdSense Integration - Setup Guide

This guide will walk you through setting up Google AdSense integration in your TaskHub application to fetch performance reports by country.

## Features

The AdSense integration provides:
- **Impressions** - Total ad impressions per country
- **Clicks** - Total clicks on ads per country
- **Page Views** - Total page views per country
- **CPC** (Cost Per Click) - Average earnings per click
- **Page RPM** (Revenue Per Mille) - Revenue per 1000 page views
- **Page CTR** (Click-Through Rate) - Percentage of page views resulting in clicks
- **Earnings** - Total earnings per country

## Important Note

Google AdSense API provides data with a delay of approximately **24-48 hours**. The most recent data you can fetch is typically from 1-2 days ago. This is NOT real-time data.

---

## Prerequisites

Before you begin, ensure you have:
1. A Google AdSense account with active ads
2. Access to Google Cloud Console
3. Composer installed (for dependencies)
4. PHP 8.0 or higher

---

## Step 1: Google Cloud Console Setup

### 1.1 Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click "Select a project" → "New Project"
3. Enter project name (e.g., "TaskHub AdSense")
4. Click "Create"

### 1.2 Enable AdSense Management API

1. In your Google Cloud project, go to **APIs & Services** → **Library**
2. Search for "AdSense Management API"
3. Click on it and click **Enable**

### 1.3 Configure OAuth Consent Screen

1. Go to **APIs & Services** → **OAuth consent screen**
2. Select **External** user type (or Internal if you have a Google Workspace account)
3. Click **Create**
4. Fill in the required fields:
   - **App name**: "TaskHub AdSense Integration"
   - **User support email**: Your email
   - **Developer contact email**: Your email
5. Click **Save and Continue**
6. On the **Scopes** page, click **Add or Remove Scopes**
7. Search for "AdSense Management API" and select:
   - `https://www.googleapis.com/auth/adsense.readonly`
8. Click **Update** and then **Save and Continue**
9. Skip the test users page and click **Save and Continue**
10. Review and click **Back to Dashboard**

### 1.4 Create OAuth 2.0 Credentials

1. Go to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **OAuth client ID**
3. Select **Application type**: **Web application**
4. Enter details:
   - **Name**: "TaskHub AdSense OAuth Client"
   - **Authorized redirect URIs**: Add your callback URL
     - For local development: `http://localhost:8000/admin/auth/google/adsense/callback`
     - For production: `https://yourdomain.com/admin/auth/google/adsense/callback`
5. Click **Create**
6. **IMPORTANT**: Copy the **Client ID** and **Client Secret** - you'll need these for the next step
7. Click **OK**

### 1.5 Important Notes

- OAuth 2.0 allows you to authenticate directly with your Google account
- No need to invite service accounts or accept invitations
- The user who authenticates must have access to the AdSense account
- The authentication is done through a secure Google login flow

---

## Step 2: Laravel Application Setup

### 2.1 Install Dependencies

The Google API Client library should already be installed. If not, run:

```bash
composer require google/apiclient
```

### 2.2 Configure Environment Variables

Add these lines to your `.env` file (use the Client ID and Client Secret from Step 1.4):

```env
# Google AdSense OAuth 2.0 Configuration
ADSENSE_CLIENT_ID=your-client-id.apps.googleusercontent.com
ADSENSE_CLIENT_SECRET=your-client-secret
ADSENSE_REDIRECT_URI=http://localhost:8000/admin/auth/google/adsense/callback
```

**For Production:**
Update the redirect URI to match your production domain:
```env
ADSENSE_REDIRECT_URI=https://yourdomain.com/admin/auth/google/adsense/callback
```

**Important:** The redirect URI must match exactly what you configured in Google Cloud Console.

### 2.3 Run Database Migration

```bash
php artisan migrate
```

This creates the `adsense_reports` table.

### 2.4 Connect Your AdSense Account

1. Log in to your TaskHub admin panel
2. Go to **Settings** → **AdSense**
3. Click **"Connect with Google"** button
4. You'll be redirected to Google's authorization page
5. Sign in with the Google account that has access to your AdSense account
6. Review the permissions and click **"Allow"**
7. You'll be redirected back to TaskHub
8. Enter your **AdSense Account ID** (format: `pub-XXXXXXXXXXXXXXXX`)
9. Click **Save**

**Finding your AdSense Account ID:**
1. Go to your [AdSense Account](https://www.google.com/adsense/)
2. Click on **Account** → **Account information**
3. Your Publisher ID is your account ID (format: `pub-XXXXXXXXXXXXXXXX`)

---

## Step 3: Testing the Integration

### 3.1 Test API Connection

Use the following endpoint to test if your AdSense API is configured correctly:

```bash
curl -X GET "http://your-domain.com/api/adsense/test-connection" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

**Expected Response (Success):**
```json
{
  "success": true,
  "message": "AdSense API connection successful",
  "accounts": [
    {
      "id": "accounts/pub-XXXXXXXXXXXXXXXX",
      "display_name": "Your AdSense Account",
      "state": "READY"
    }
  ]
}
```

**Expected Response (Failure):**
```json
{
  "success": false,
  "message": "AdSense API connection error: [error details]"
}
```

### 3.2 Sync Your First Report

Sync yesterday's data (most recent available):

```bash
curl -X POST "http://your-domain.com/api/adsense/sync" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "start_date": "2025-01-14",
    "end_date": "2025-01-14"
  }'
```

**Note:** Use dates from 2-3 days ago for better results due to AdSense data delay.

---

## Step 4: Using the API

### 4.1 Available Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/adsense/reports` | Get reports for date range |
| GET | `/api/adsense/reports/by-country` | Get reports grouped by country |
| GET | `/api/adsense/reports/summary` | Get summary statistics |
| GET | `/api/adsense/reports/latest-date` | Get latest report date |
| POST | `/api/adsense/sync` | Sync data from AdSense API |
| GET | `/api/adsense/test-connection` | Test API connection |

### 4.2 Get Reports by Country

```bash
curl -X GET "http://your-domain.com/api/adsense/reports/by-country?start_date=2025-01-10&end_date=2025-01-14" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "country_code": "US",
      "country_name": "United States",
      "total_impressions": 50000,
      "total_clicks": 250,
      "total_page_views": 30000,
      "avg_cpc": 0.45,
      "avg_page_rpm": 2.5,
      "avg_page_ctr": 0.83,
      "total_earnings": 112.50
    },
    {
      "country_code": "GB",
      "country_name": "United Kingdom",
      "total_impressions": 25000,
      "total_clicks": 120,
      "total_page_views": 15000,
      "avg_cpc": 0.38,
      "avg_page_rpm": 2.1,
      "avg_page_ctr": 0.80,
      "total_earnings": 45.60
    }
  ],
  "meta": {
    "start_date": "2025-01-10",
    "end_date": "2025-01-14",
    "total_countries": 2
  }
}
```

### 4.3 Get Summary Statistics

```bash
curl -X GET "http://your-domain.com/api/adsense/reports/summary?start_date=2025-01-10&end_date=2025-01-14" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_impressions": 75000,
    "total_clicks": 370,
    "total_page_views": 45000,
    "avg_cpc": 0.4167,
    "avg_page_rpm": 2.3,
    "avg_page_ctr": 0.82,
    "total_earnings": 158.10,
    "total_countries": 2
  },
  "meta": {
    "start_date": "2025-01-10",
    "end_date": "2025-01-14"
  }
}
```

---

## Step 5: Automating Data Sync

### 5.1 Create Artisan Command

Create a command to sync data daily:

```bash
php artisan make:command SyncAdSenseData
```

Edit `app/Console/Commands/SyncAdSenseData.php`:

```php
<?php

namespace App\Console\Commands;

use App\Services\AdSenseService;
use Illuminate\Console\Command;

class SyncAdSenseData extends Command
{
    protected $signature = 'adsense:sync {--date= : Date to sync (Y-m-d)}';
    protected $description = 'Sync AdSense data for a specific date';

    public function handle(AdSenseService $adSenseService)
    {
        $date = $this->option('date') ?: now()->subDays(2)->toDateString();

        $this->info("Syncing AdSense data for {$date}...");

        try {
            $reports = $adSenseService->syncDateRange($date, $date);
            $this->info("Successfully synced " . count($reports) . " records");
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
```

### 5.2 Schedule in Kernel

Edit `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync AdSense data daily at 4 AM (data from 2 days ago)
    $schedule->command('adsense:sync')->dailyAt('04:00');
}
```

Make sure your cron job is set up (see `CRON_SETUP.md` for details):

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Troubleshooting

### Problem: "OAuth authorization failed"

**Solution:**
1. Verify that the redirect URI in `.env` matches exactly what's in Google Cloud Console
2. Make sure you're logged in with the Google account that has AdSense access
3. Check that the AdSense Management API is enabled in your Google Cloud project
4. Verify that the OAuth consent screen is configured correctly

### Problem: "Invalid client ID or secret"

**Solution:**
1. Double-check the Client ID and Client Secret in your `.env` file
2. Make sure there are no extra spaces or quotes
3. Regenerate the OAuth credentials if needed from Google Cloud Console

### Problem: "No data returned"

**Possible Causes:**
1. **Too recent date** - Try dates from 2-3 days ago
2. **No ad activity** - Ensure your ads are running and getting traffic
3. **Account not active** - Verify your AdSense account is active

### Problem: "Access token expired"

**Solution:**
The system automatically refreshes expired tokens using the refresh token. If this fails:
1. Disconnect and reconnect your Google account from Settings → AdSense
2. Make sure the refresh token is being stored correctly in the database

### Problem: "API quota exceeded"

**Solution:**
The AdSense API has rate limits. Use caching (already implemented with 6-hour cache) and avoid excessive API calls.

---

## Security Best Practices

1. **Never commit** OAuth credentials to version control
2. Keep Client ID and Client Secret in `.env` file only
3. Use environment variables for sensitive data
4. Restrict API access with Laravel permissions/roles
5. Use HTTPS in production for OAuth callbacks
6. Regularly review authorized applications in your Google account settings

---

## Data Retention

The database stores all synced reports. Consider implementing data retention policies:

```php
// Example: Delete reports older than 1 year
AdSenseReport::where('report_date', '<', now()->subYear())->delete();
```

---

## Next Steps

1. **Build Frontend UI** - Create Vue/React components to display reports
2. **Add Charts** - Visualize data with charts (Chart.js, ApexCharts)
3. **Export Features** - Add CSV/Excel export
4. **Email Reports** - Schedule weekly/monthly email summaries
5. **Alerts** - Set up alerts for significant changes

---

## Support

For issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check AdSense Management API quotas in Google Cloud Console
3. Verify service account permissions in AdSense

## API Reference

For complete AdSense Management API documentation:
- [AdSense Management API](https://developers.google.com/adsense/management/reference/rest)
- [OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server)

---

## Summary

✅ **Backend Complete** - API endpoints, service class, database
✅ **Configuration** - Environment variables, credentials
✅ **Testing** - Connection test endpoint
✅ **Automation** - Scheduled daily syncs
⏳ **Frontend** - Next step: Build UI components

You're now ready to integrate AdSense reporting into your application!
