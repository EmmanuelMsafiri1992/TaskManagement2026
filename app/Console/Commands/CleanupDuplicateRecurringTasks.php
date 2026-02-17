<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateRecurringTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:cleanup-duplicates {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate recurring tasks created by the old cloning system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Scanning for duplicate recurring tasks...');

        // Find all tasks with recurring meta (these are the ones we want to keep)
        $recurringTasks = Task::all()->filter(function ($task) {
            return isset($task->meta['recurring']);
        });

        $this->info("Found {$recurringTasks->count()} tasks with recurring configuration");

        // Group by title and project_id to find duplicates
        $grouped = $recurringTasks->groupBy(function ($task) {
            return $task->project_id . '_' . $task->title;
        });

        $totalDuplicates = 0;
        $toDelete = collect();

        foreach ($grouped as $key => $tasks) {
            if ($tasks->count() > 1) {
                $this->warn("Duplicate found: {$tasks->first()->title} ({$tasks->count()} copies)");

                // Keep the most recent one (highest ID), delete the rest
                $sorted = $tasks->sortByDesc('id');
                $keep = $sorted->first();
                $duplicates = $sorted->slice(1);

                $this->line("  Keeping ID: {$keep->id}");

                foreach ($duplicates as $duplicate) {
                    $this->line("  Will delete ID: {$duplicate->id}");
                    $toDelete->push($duplicate);
                    $totalDuplicates++;
                }
            }
        }

        // Also find tasks with replicated_at that have same title but no recurring
        // These are the "orphaned" original tasks that lost their recurring config
        $this->info("\nScanning for orphaned original tasks...");

        $replicatedTasks = Task::whereNotNull('replicated_at')
            ->whereNull('deleted_at')
            ->get();

        foreach ($replicatedTasks as $task) {
            // Check if there's another task with same title in same project that has recurring
            $hasRecurringVersion = Task::where('project_id', $task->project_id)
                ->where('title', $task->title)
                ->where('id', '!=', $task->id)
                ->whereNull('deleted_at')
                ->get()
                ->filter(function ($t) {
                    return isset($t->meta['recurring']);
                })
                ->isNotEmpty();

            if ($hasRecurringVersion && !isset($task->meta['recurring'])) {
                $this->line("  Orphaned task ID: {$task->id} - {$task->title}");
                $toDelete->push($task);
                $totalDuplicates++;
            }
        }

        $this->newLine();
        $this->info("Total duplicates/orphans found: {$totalDuplicates}");

        if ($totalDuplicates === 0) {
            $this->info('No duplicates to clean up!');
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('DRY RUN - No tasks were deleted. Run without --dry-run to actually delete.');
            return Command::SUCCESS;
        }

        if (!$this->confirm("Delete {$totalDuplicates} duplicate tasks?")) {
            $this->info('Aborted.');
            return Command::SUCCESS;
        }

        // Perform deletion
        DB::beginTransaction();
        try {
            $deleted = 0;
            foreach ($toDelete as $task) {
                // Detach relationships
                $task->users()->detach();
                $task->labels()->detach();

                // Delete checklists and their items
                foreach ($task->checklists as $checklist) {
                    $checklist->checklistItems()->delete();
                    $checklist->delete();
                }

                // Force delete the task
                $task->forceDelete();
                $deleted++;
            }

            DB::commit();

            $this->info("Successfully deleted {$deleted} duplicate tasks!");

            Log::info('Duplicate recurring tasks cleanup completed', [
                'deleted_count' => $deleted,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: {$e->getMessage()}");
            Log::error('Duplicate recurring tasks cleanup failed', [
                'error' => $e->getMessage(),
            ]);
            return Command::FAILURE;
        }
    }
}
