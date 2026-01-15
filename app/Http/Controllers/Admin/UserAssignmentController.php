<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFocus;
use App\Models\UserAssignment;
use App\Models\V11User;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectList;
use App\Notifications\NewUserAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserAssignmentController extends Controller
{
    /**
     * Get all TaskHub users with their focus and assignment counts
     */
    public function index()
    {
        $users = User::with(['roles', 'focus'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Marketer', 'Developer', 'Admin']);
            })
            ->get()
            ->map(function ($user) {
                $focus = $user->focus ?? null;
                $jobSeekersCount = UserAssignment::where('taskhub_user_id', $user->id)
                    ->where('v11_user_type', 2)
                    ->count();
                $employersCount = UserAssignment::where('taskhub_user_id', $user->id)
                    ->where('v11_user_type', 1)
                    ->count();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()->name ?? null,
                    'focus' => $focus ? [
                        'works_with_job_seekers' => $focus->works_with_job_seekers,
                        'works_with_employers' => $focus->works_with_employers,
                        'enable_auto_assign_job_seekers' => $focus->enable_auto_assign_job_seekers,
                        'enable_auto_assign_employers' => $focus->enable_auto_assign_employers,
                    ] : null,
                    'job_seekers_count' => $jobSeekersCount,
                    'employers_count' => $employersCount,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Update user's focus settings
     */
    public function updateFocus(Request $request, $userId)
    {
        $request->validate([
            'works_with_job_seekers' => 'required|boolean',
            'works_with_employers' => 'required|boolean',
            'enable_auto_assign_job_seekers' => 'required|boolean',
            'enable_auto_assign_employers' => 'required|boolean',
        ]);

        $user = User::findOrFail($userId);

        $focus = $user->focus()->updateOrCreate(
            ['user_id' => $userId],
            $request->only([
                'works_with_job_seekers',
                'works_with_employers',
                'enable_auto_assign_job_seekers',
                'enable_auto_assign_employers',
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'User focus updated successfully',
            'data' => $focus,
        ]);
    }

    /**
     * Get available V11 users (job seekers or employers)
     */
    public function getAvailableUsers(Request $request)
    {
        try {
            $type = $request->input('type'); // 'job_seekers' or 'employers'
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);
            $search = $request->input('search', '');

            $query = V11User::verified();

            if ($type === 'job_seekers') {
                $query->jobSeekers();
            } elseif ($type === 'employers') {
                $query->employers();
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $totalCount = $query->count();
            $users = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get(['id', 'name', 'email', 'user_type_id', 'created_at']);

            // Check if users are already assigned
            $usersWithStatus = $users->map(function ($user) {
                $assignment = UserAssignment::where('v11_user_id', $user->id)
                    ->where('v11_user_type', $user->user_type_id)
                    ->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type_id,
                    'user_type_name' => $user->getUserTypeName(),
                    'created_at' => $user->created_at,
                    'is_assigned' => $assignment !== null,
                    'assigned_to' => $assignment ? [
                        'id' => $assignment->taskhub_user_id,
                        'name' => $assignment->taskhubUser->name,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $usersWithStatus,
                'meta' => [
                    'total' => $totalCount,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($totalCount / $perPage),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'per_page' => 20,
                    'current_page' => 1,
                    'last_page' => 1,
                ],
            ]);
        }
    }

    /**
     * Get user's assignments
     */
    public function getUserAssignments($userId)
    {
        $user = User::findOrFail($userId);

        $assignments = UserAssignment::where('taskhub_user_id', $userId)
            ->with(['assignedBy', 'task'])
            ->orderBy('assigned_at', 'desc')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'task_id' => $assignment->task_id,
                    'task' => $assignment->task ? [
                        'id' => $assignment->task->id,
                        'project_id' => $assignment->task->project_id,
                        'title' => $assignment->task->title,
                    ] : null,
                    'v11_user_id' => $assignment->v11_user_id,
                    'v11_user_type' => $assignment->v11_user_type,
                    'v11_user_name' => $assignment->v11_user_name,
                    'v11_user_email' => $assignment->v11_user_email,
                    'user_type_name' => $assignment->getUserTypeName(),
                    'auto_assigned' => $assignment->auto_assigned,
                    'assigned_by' => $assignment->assignedBy ? $assignment->assignedBy->name : null,
                    'assigned_at' => $assignment->assigned_at,
                    'last_contacted_at' => $assignment->last_contacted_at,
                    'notes' => $assignment->notes,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    /**
     * Assign V11 user to TaskHub user
     */
    public function assignUser(Request $request)
    {
        $request->validate([
            'taskhub_user_id' => 'required|exists:users,id',
            'v11_user_id' => 'required|integer',
            'v11_user_type' => 'required|in:1,2',
        ]);

        // Check if already assigned
        $existing = UserAssignment::where('v11_user_id', $request->v11_user_id)
            ->where('v11_user_type', $request->v11_user_type)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This user is already assigned to ' . $existing->taskhubUser->name,
            ], 400);
        }

        // Get V11 user details
        try {
            $v11User = V11User::find($request->v11_user_id);
            if (!$v11User || !$v11User->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or email not verified',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'V11 database not available',
            ], 500);
        }

        // Get project and list for user assignments
        $project = Project::first();
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'No project found. Please create a project first.',
            ], 404);
        }

        $projectList = ProjectList::where('project_id', $project->id)->first();
        if (!$projectList) {
            return response()->json([
                'success' => false,
                'message' => 'No project list found. Please create a list in the project first.',
            ], 404);
        }

        // Create a task for this assignment
        $userTypeName = $request->v11_user_type == 1 ? 'Employer' : 'Job Seeker';
        $task = Task::create([
            'project_id' => $project->id,
            'project_list_id' => $projectList->id,
            'title' => "Follow up with {$userTypeName}: {$v11User->name}",
            'description' => "Contact and follow up with {$userTypeName}.\n\nName: {$v11User->name}\nEmail: {$v11User->email}\n\nPlease reach out and engage with this user.",
            'order' => Task::where('project_list_id', $projectList->id)->max('order') + 1,
            'total_seconds' => 0,
        ]);

        // Assign task to the TaskHub user
        $task->users()->attach($request->taskhub_user_id);

        $assignment = UserAssignment::create([
            'taskhub_user_id' => $request->taskhub_user_id,
            'task_id' => $task->id,
            'v11_user_id' => $request->v11_user_id,
            'v11_user_type' => $request->v11_user_type,
            'v11_user_name' => $v11User->name,
            'v11_user_email' => $v11User->email,
            'auto_assigned' => false,
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
        ]);

        // Send notification to TaskHub user
        $taskhubUser = User::find($request->taskhub_user_id);
        $taskhubUser->notify(new NewUserAssigned($assignment));

        return response()->json([
            'success' => true,
            'message' => 'User assigned successfully',
            'data' => $assignment,
        ]);
    }

    /**
     * Unassign V11 user
     */
    public function unassignUser($assignmentId)
    {
        $assignment = UserAssignment::findOrFail($assignmentId);
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'User unassigned successfully',
        ]);
    }

    /**
     * Update contact notes
     */
    public function updateNotes(Request $request, $assignmentId)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'contacted' => 'boolean',
        ]);

        $assignment = UserAssignment::findOrFail($assignmentId);

        if ($request->has('notes')) {
            $assignment->notes = $request->notes;
        }

        if ($request->contacted) {
            $assignment->last_contacted_at = now();
        }

        $assignment->save();

        return response()->json([
            'success' => true,
            'message' => 'Notes updated successfully',
            'data' => $assignment,
        ]);
    }

    /**
     * Get assignment statistics
     */
    public function getStatistics()
    {
        try {
            $totalJobSeekers = V11User::verified()->jobSeekers()->count();
            $totalEmployers = V11User::verified()->employers()->count();
        } catch (\Exception $e) {
            $totalJobSeekers = 0;
            $totalEmployers = 0;
        }

        $assignedJobSeekers = UserAssignment::where('v11_user_type', 2)->count();
        $assignedEmployers = UserAssignment::where('v11_user_type', 1)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'job_seekers' => [
                    'total' => $totalJobSeekers,
                    'assigned' => $assignedJobSeekers,
                    'unassigned' => max(0, $totalJobSeekers - $assignedJobSeekers),
                ],
                'employers' => [
                    'total' => $totalEmployers,
                    'assigned' => $assignedEmployers,
                    'unassigned' => max(0, $totalEmployers - $assignedEmployers),
                ],
            ],
        ]);
    }
}
