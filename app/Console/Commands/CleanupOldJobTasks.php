<?php

namespace App\Console\Commands;

use App\Models\JobShare;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldJobTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:cleanup-old-tasks
                            {--project=18 : Project ID to clean up}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old job sharing tasks with incorrect links';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $projectId = (int) $this->option('project');
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? 'DRY RUN - No changes will be made' : 'Cleaning up old job tasks...');
        $this->newLine();

        // Find all tasks in the project that have job shares
        $jobShares = JobShare::with('task')->get();

        if ($jobShares->isEmpty()) {
            $this->info('No job share records found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$jobShares->count()} job share record(s).");

        $tasksDeleted = 0;
        $jobSharesDeleted = 0;

        DB::beginTransaction();

        try {
            foreach ($jobShares as $jobShare) {
                $task = $jobShare->task;

                if (!$task) {
                    // Job share without task - delete it
                    $this->line("  - Deleting orphan job share #{$jobShare->id}");
                    if (!$dryRun) {
                        $jobShare->delete();
                    }
                    $jobSharesDeleted++;
                    continue;
                }

                // Check if task belongs to our project
                if ($task->project_id != $projectId) {
                    continue;
                }

                $this->line("  - Deleting task #{$task->id}: {$task->title}");
                $this->line("    Job Share #{$jobShare->id} for post #{$jobShare->v11_post_id}");

                if (!$dryRun) {
                    // Delete task (this will cascade or we handle relations)
                    $task->users()->detach();
                    $task->delete();
                    $jobShare->delete();
                }

                $tasksDeleted++;
                $jobSharesDeleted++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            $this->newLine();
            $this->info("Summary:");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Tasks Deleted', $tasksDeleted],
                    ['Job Shares Deleted', $jobSharesDeleted],
                ]
            );

            if ($dryRun) {
                $this->newLine();
                $this->warn('This was a dry run. Run without --dry-run to actually delete.');
            } else {
                $this->newLine();
                $this->info('Cleanup completed successfully!');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Cleanup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
