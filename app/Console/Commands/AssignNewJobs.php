<?php

namespace App\Console\Commands;

use App\Services\JobAssignmentService;
use Illuminate\Console\Command;

class AssignNewJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:assign-new
                            {--hours=24 : Number of hours to look back for jobs}
                            {--limit= : Maximum number of jobs to process}
                            {--force : Force assignment even if run recently}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch new jobs from V11 database and assign them to users based on their countries';

    /**
     * @var JobAssignmentService
     */
    protected $jobAssignmentService;

    /**
     * Create a new command instance.
     *
     * @param  JobAssignmentService  $jobAssignmentService
     * @return void
     */
    public function __construct(JobAssignmentService $jobAssignmentService)
    {
        parent::__construct();
        $this->jobAssignmentService = $jobAssignmentService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->info("Starting job assignment process (looking back {$hours} hours)...");
        $this->newLine();

        try {
            $stats = $this->jobAssignmentService->assignNewJobs($hours, $limit);

            $this->displayResults($stats);

            if ($stats['errors'] > 0) {
                $this->warn("Completed with {$stats['errors']} error(s). Check logs for details.");
                return Command::FAILURE;
            }

            $this->info('Job assignment process completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Job assignment process failed: ' . $e->getMessage());
            $this->newLine();
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * Display assignment results.
     *
     * @param  array  $stats
     * @return void
     */
    protected function displayResults(array $stats)
    {
        $this->info('📊 Assignment Results:');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Posts Fetched', $stats['posts_fetched']],
                ['Assignments Created', $stats['assignments_created']],
                ['Tasks Created', $stats['tasks_created']],
                ['Notifications Sent', $stats['notifications_sent']],
                ['Errors', $stats['errors']],
            ]
        );

        $this->newLine();

        if ($stats['assignments_created'] > 0) {
            $this->info("✅ Successfully assigned {$stats['assignments_created']} job(s) to users");
        } else {
            $this->comment('ℹ️  No new jobs were assigned (either no new posts or all already assigned)');
        }

        $this->newLine();
    }
}
