<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UserAssignment;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectList;

echo "\n=== Creating Tasks for Existing Assignments ===\n\n";

// Get assignments without tasks
$assignmentsWithoutTasks = UserAssignment::whereNull('task_id')->get();

echo "Found {$assignmentsWithoutTasks->count()} assignments without tasks.\n\n";

if ($assignmentsWithoutTasks->isEmpty()) {
    echo "Nothing to do!\n";
    exit;
}

// Get project and list
$project = Project::first();
if (!$project) {
    echo "ERROR: No project found. Please create a project first.\n";
    exit;
}

echo "Using project: {$project->name} (ID: {$project->id})\n";

$projectList = ProjectList::where('project_id', $project->id)->first();
if (!$projectList) {
    echo "ERROR: No project list found. Please create a list in the project first.\n";
    exit;
}

echo "Using list: {$projectList->title} (ID: {$projectList->id})\n\n";

$created = 0;
$failed = 0;

foreach ($assignmentsWithoutTasks as $assignment) {
    try {
        $userTypeName = $assignment->getUserTypeName();

        // Create a task for this assignment
        $task = Task::create([
            'project_id' => $project->id,
            'project_list_id' => $projectList->id,
            'title' => "Follow up with {$userTypeName}: {$assignment->v11_user_name}",
            'description' => "Contact and follow up with {$userTypeName}.\n\nName: {$assignment->v11_user_name}\nEmail: {$assignment->v11_user_email}\n\nPlease reach out and engage with this user.",
            'order' => Task::where('project_list_id', $projectList->id)->max('order') + 1,
            'total_seconds' => 0,
        ]);

        // Assign task to the TaskHub user
        $task->users()->attach($assignment->taskhub_user_id);

        // Update assignment with task_id
        $assignment->update(['task_id' => $task->id]);

        echo "✓ Created task #{$task->id} for {$userTypeName}: {$assignment->v11_user_name} (assigned to {$assignment->taskhubUser->name})\n";
        $created++;

    } catch (\Exception $e) {
        echo "✗ Failed to create task for assignment #{$assignment->id}: {$e->getMessage()}\n";
        $failed++;
    }
}

echo "\n=== Summary ===\n";
echo "Created: {$created}\n";
echo "Failed: {$failed}\n";
echo "\n";
