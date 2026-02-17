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

        $this->info('Scanning for ALL duplicate tasks by title within same project list...');

        // Get all non-deleted tasks grouped by project_list_id and title
        $allTasks = Task::whereNull('deleted_at')->get();

        // Group by project_list_id + title to find duplicates
        $grouped = $allTasks->groupBy(function ($task) {
            return $task->project_list_id . '_' . $task->title;
        });

        $totalDuplicates = 0;
        $toDelete = collect();

        foreach ($grouped as $key => $tasks) {
            if ($tasks->count() > 1) {
                $first = $tasks->first();
                $this->warn("Duplicate found: \"{$first->title}\" in list {$first->project_list_id} ({$tasks->count()} copies)");

                // Prefer to keep the one with recurring meta, otherwise keep the oldest (lowest ID)
                $withRecurring = $tasks->first(function ($t) {
                    return isset($t->meta['recurring']);
                });

                if ($withRecurring) {
                    $keep = $withRecurring;
                } else {
                    // Keep the oldest one (lowest ID) - it's likely the original
                    $keep = $tasks->sortBy('id')->first();
                }

                $this->line("  Keeping ID: {$keep->id}" . (isset($keep->meta['recurring']) ? ' (has recurring)' : ''));

                foreach ($tasks as $task) {
                    if ($task->id !== $keep->id) {
                        $this->line("  Will delete ID: {$task->id}");
                        $toDelete->push($task);
                        $totalDuplicates++;
                    }
                }
            }
        }

        $this->newLine();
        $this->info("Total duplicates found: {$totalDuplicates}");

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

                // Delete comments
                $task->comments()->delete();

                // Delete attachments
                $task->attachments()->delete();

                // Delete time logs
                $task->timelogs()->delete();

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
