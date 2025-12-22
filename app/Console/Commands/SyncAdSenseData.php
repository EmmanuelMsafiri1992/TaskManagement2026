<?php

namespace App\Console\Commands;

use App\Services\AdSenseService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAdSenseData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adsense:sync {--date= : Specific date to sync (Y-m-d)} {--start-date= : Start date for range} {--end-date= : End date for range}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync AdSense performance data from Google AdSense API';

    protected $adSenseService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AdSenseService $adSenseService)
    {
        parent::__construct();
        $this->adSenseService = $adSenseService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting AdSense data sync...');

        try {
            // Determine date range
            if ($this->option('date')) {
                $startDate = $this->option('date');
                $endDate = $this->option('date');
            } elseif ($this->option('start-date') && $this->option('end-date')) {
                $startDate = $this->option('start-date');
                $endDate = $this->option('end-date');
            } else {
                // Default: sync data from 2 days ago (most recent available)
                $startDate = Carbon::now()->subDays(2)->toDateString();
                $endDate = $startDate;
            }

            $this->line("Syncing data for period: {$startDate} to {$endDate}");

            // Fetch and process reports
            $reports = $this->adSenseService->syncDateRange($startDate, $endDate);

            $this->newLine();
            $this->info("✓ Successfully synced " . count($reports) . " country records");

            // Show summary
            if (count($reports) > 0) {
                $totalImpressions = array_sum(array_column($reports, 'impressions'));
                $totalClicks = array_sum(array_column($reports, 'clicks'));
                $totalEarnings = array_sum(array_column($reports, 'earnings'));

                $this->newLine();
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Countries', count($reports)],
                        ['Total Impressions', number_format($totalImpressions)],
                        ['Total Clicks', number_format($totalClicks)],
                        ['Total Earnings', '$' . number_format($totalEarnings, 2)],
                    ]
                );
            }

            Log::info('AdSense sync completed', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'records' => count($reports),
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());

            Log::error('AdSense sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
