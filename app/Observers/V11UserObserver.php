<?php

namespace App\Observers;

use App\Models\V11User;
use App\Models\User;
use App\Models\UserAssignment;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectList;
use App\Notifications\NewUserAssigned;
use Illuminate\Support\Facades\Log;

class V11UserObserver
{
    /**
     * Handle the V11User "created" event.
     *
     * @param  \App\Models\V11User  $v11User
     * @return void
     */
    public function created(V11User $v11User)
    {
        // Only process verified users
        if (!$v11User->email_verified_at) {
            return;
        }

        // Determine if this is a job seeker (2) or employer (1)
        $userType = $v11User->user_type_id;

        // Get eligible TaskHub users for auto-assignment
        $eligibleUsers = $this->getEligibleUsers($userType);

        if ($eligibleUsers->isEmpty()) {
            Log::info("No eligible users found for auto-assignment", [
                'v11_user_id' => $v11User->id,
                'user_type' => $userType
            ]);
            return;
        }

        // Select user with least assignments (round-robin based on workload)
        $selectedUser = $this->selectUserWithLeastAssignments($eligibleUsers, $userType);

        if (!$selectedUser) {
            Log::warning("Could not select user for auto-assignment", [
                'v11_user_id' => $v11User->id,
                'user_type' => $userType
            ]);
            return;
        }

        // Create the assignment with task
        try {
            // Get project and list for user assignments
            $project = Project::first();
            if (!$project) {
                Log::error("No project found for auto-assignment", [
                    'v11_user_id' => $v11User->id
                ]);
                return;
            }

            $projectList = ProjectList::where('project_id', $project->id)->first();
            if (!$projectList) {
                Log::error("No project list found for auto-assignment", [
                    'v11_user_id' => $v11User->id,
                    'project_id' => $project->id
                ]);
                return;
            }

            // Create a task for this assignment
            $userTypeName = $userType == 1 ? 'Employer' : 'Job Seeker';
            $task = Task::create([
                'project_id' => $project->id,
                'project_list_id' => $projectList->id,
                'title' => "Follow up with {$userTypeName}: {$v11User->name}",
                'description' => "Contact and follow up with {$userTypeName}.\n\nName: {$v11User->name}\nEmail: {$v11User->email}\n\nPlease reach out and engage with this user.",
                'order' => Task::where('project_list_id', $projectList->id)->max('order') + 1,
                'total_seconds' => 0,
            ]);

            // Assign task to the TaskHub user
            $task->users()->attach($selectedUser->id);

            $assignment = UserAssignment::create([
                'taskhub_user_id' => $selectedUser->id,
                'task_id' => $task->id,
                'v11_user_id' => $v11User->id,
                'v11_user_type' => $userType,
                'v11_user_name' => $v11User->name,
                'v11_user_email' => $v11User->email,
                'auto_assigned' => true,
                'assigned_by' => null, // System assignment
                'assigned_at' => now(),
            ]);

            // Send notification to the assigned TaskHub user
            $selectedUser->notify(new NewUserAssigned($assignment));

            Log::info("Auto-assigned V11 user with task", [
                'v11_user_id' => $v11User->id,
                'v11_user_name' => $v11User->name,
                'taskhub_user_id' => $selectedUser->id,
                'taskhub_user_name' => $selectedUser->name,
                'task_id' => $task->id,
                'user_type' => $userType
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to auto-assign V11 user", [
                'v11_user_id' => $v11User->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get TaskHub users eligible for auto-assignment
     *
     * @param int $userType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getEligibleUsers($userType)
    {
        return User::whereHas('focus', function ($query) use ($userType) {
            if ($userType == 2) { // Job Seeker
                $query->where('works_with_job_seekers', true)
                    ->where('enable_auto_assign_job_seekers', true);
            } else { // Employer
                $query->where('works_with_employers', true)
                    ->where('enable_auto_assign_employers', true);
            }
        })->with('focus')->get();
    }

    /**
     * Select user with least assignments of this type
     *
     * @param \Illuminate\Database\Eloquent\Collection $users
     * @param int $userType
     * @return \App\Models\User|null
     */
    protected function selectUserWithLeastAssignments($users, $userType)
    {
        if ($users->isEmpty()) {
            return null;
        }

        $userWithLeastAssignments = null;
        $minAssignments = PHP_INT_MAX;

        foreach ($users as $user) {
            $assignmentCount = UserAssignment::where('taskhub_user_id', $user->id)
                ->where('v11_user_type', $userType)
                ->count();

            if ($assignmentCount < $minAssignments) {
                $minAssignments = $assignmentCount;
                $userWithLeastAssignments = $user;
            }
        }

        return $userWithLeastAssignments;
    }

    /**
     * Handle the V11User "updated" event.
     * If email is verified for the first time, trigger assignment
     *
     * @param  \App\Models\V11User  $v11User
     * @return void
     */
    public function updated(V11User $v11User)
    {
        // Check if email was just verified
        if ($v11User->wasChanged('email_verified_at') && $v11User->email_verified_at) {
            // Check if already assigned
            $existingAssignment = UserAssignment::where('v11_user_id', $v11User->id)
                ->where('v11_user_type', $v11User->user_type_id)
                ->exists();

            if (!$existingAssignment) {
                // Trigger the auto-assignment logic
                $this->created($v11User);
            }
        }
    }
}
