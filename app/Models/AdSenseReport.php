<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdSenseReport extends Model
{
    use HasFactory;

    protected $table = 'adsense_reports';

    protected $fillable = [
        'report_date',
        'country_code',
        'country_name',
        'impressions',
        'clicks',
        'page_views',
        'cpc',
        'page_rpm',
        'page_ctr',
        'earnings',
    ];

    protected $casts = [
        'report_date' => 'date',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'page_views' => 'integer',
        'cpc' => 'decimal:4',
        'page_rpm' => 'decimal:4',
        'page_ctr' => 'decimal:4',
        'earnings' => 'decimal:2',
    ];

    /**
     * Get reports for a specific date range
     */
    public static function getByDateRange($startDate, $endDate)
    {
        return static::whereBetween('report_date', [$startDate, $endDate])
                     ->orderBy('report_date', 'desc')
                     ->orderBy('impressions', 'desc')
                     ->get();
    }

    /**
     * Get reports grouped by country for a date range
     */
    public static function getByCountry($startDate, $endDate)
    {
        return static::selectRaw('
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
            ->orderBy('total_impressions', 'desc')
            ->get();
    }

    /**
     * Get latest report date
     */
    public static function getLatestReportDate()
    {
        return static::max('report_date');
    }
}
