<?php

namespace App\Services;

use App\Models\JobShare;
use App\Models\Project;
use App\Models\ProjectList;
use App\Models\Task;
use App\Models\User;
use App\Models\UserCountry;
use App\Models\V11\Post;
use App\Notifications\NewJobAssigned;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobAssignmentService
{
    /**
     * @var UrlShortenerService
     */
    protected $urlShortener;

    /**
     * Constructor.
     *
     * @param  UrlShortenerService  $urlShortener
     */
    public function __construct(UrlShortenerService $urlShortener)
    {
        $this->urlShortener = $urlShortener;
    }

    /**
     * Fetch and assign new jobs to users based on their countries.
     *
     * @param  int  $hours  Number of hours to look back (default: 24)
     * @param  int|null  $limit  Maximum number of posts to process
     * @return array Statistics about the assignment process
     */
    public function assignNewJobs($hours = 24, $limit = null)
    {
        $stats = [
            'posts_fetched' => 0,
            'assignments_created' => 0,
            'tasks_created' => 0,
            'notifications_sent' => 0,
            'errors' => 0,
        ];

        try {
            // Fetch recent posts from V11 database
            $query = Post::recent($hours);
            if ($limit) {
                $query->limit($limit);
            }
            $recentPosts = $query->get();
            $stats['posts_fetched'] = $recentPosts->count();

            Log::info('Fetched recent job posts', ['count' => $stats['posts_fetched']]);

            if ($recentPosts->isEmpty()) {
                Log::info('No recent posts found');
                return $stats;
            }

            // Get all users with their assigned countries
            $usersWithCountries = $this->getUsersWithCountries();

            if (empty($usersWithCountries)) {
                Log::info('No users with assigned countries found');
                return $stats;
            }

            // Process each post
            foreach ($recentPosts as $post) {
                try {
                    $assignmentResult = $this->assignPostToUsers($post, $usersWithCountries);
                    $stats['assignments_created'] += $assignmentResult['assignments'];
                    $stats['tasks_created'] += $assignmentResult['tasks'];
                    $stats['notifications_sent'] += $assignmentResult['notifications'];
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Failed to assign post', [
                        'post_id' => $post->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Job assignment completed', $stats);

            return $stats;
        } catch (\Exception $e) {
            Log::error('Job assignment process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get all users with their assigned countries.
     *
     * @return array
     */
    protected function getUsersWithCountries()
    {
        $userCountries = UserCountry::with('user')
            ->whereHas('user', function ($query) {
                // Only get active users
                $query->whereNotNull('email_verified_at');
            })
            ->get();

        $usersWithCountries = [];

        foreach ($userCountries as $userCountry) {
            $userId = $userCountry->user_id;

            if (!isset($usersWithCountries[$userId])) {
                $usersWithCountries[$userId] = [
                    'user' => $userCountry->user,
                    'countries' => [],
                ];
            }

            $usersWithCountries[$userId]['countries'][] = $userCountry->country_code;
        }

        return $usersWithCountries;
    }

    /**
     * Maximum jobs per country per user within 24 hours.
     */
    protected const MAX_JOBS_PER_COUNTRY = 5;

    /**
     * Assign a post to all eligible users.
     *
     * @param  Post  $post
     * @param  array  $usersWithCountries
     * @return array
     */
    protected function assignPostToUsers(Post $post, array $usersWithCountries)
    {
        $stats = [
            'assignments' => 0,
            'tasks' => 0,
            'notifications' => 0,
        ];

        foreach ($usersWithCountries as $userData) {
            $user = $userData['user'];
            $countries = $userData['countries'];

            // Check if post country matches user's assigned countries
            if (!in_array($post->country_code, $countries)) {
                continue;
            }

            // Check if this post is already shared by this user (prevent duplicates)
            $existingShare = JobShare::where('user_id', $user->id)
                ->where('v11_post_id', $post->id)
                ->exists();

            if ($existingShare) {
                Log::debug('Post already shared by user', [
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]);
                continue;
            }

            // Check per-country limit (max 5 jobs per country per user in last 24 hours)
            $countryJobCount = JobShare::where('user_id', $user->id)
                ->where('country_code', $post->country_code)
                ->where('assigned_at', '>=', now()->subHours(24))
                ->count();

            if ($countryJobCount >= self::MAX_JOBS_PER_COUNTRY) {
                Log::debug('User reached max jobs for country', [
                    'user_id' => $user->id,
                    'country_code' => $post->country_code,
                    'current_count' => $countryJobCount,
                    'max_allowed' => self::MAX_JOBS_PER_COUNTRY,
                ]);
                continue;
            }

            // Assign the post to the user
            try {
                $this->createJobAssignment($user, $post);
                $stats['assignments']++;
                $stats['tasks']++;
                $stats['notifications']++;
            } catch (\Exception $e) {
                Log::error('Failed to create job assignment', [
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    /**
     * Create a job assignment for a user.
     *
     * @param  User  $user
     * @param  Post  $post
     * @return JobShare
     */
    protected function createJobAssignment(User $user, Post $post)
    {
        return DB::transaction(function () use ($user, $post) {
            // Generate shortened URL
            $postUrl = $post->getUrl();
            $shortenedUrl = $this->urlShortener->shorten($postUrl, $user->id);
            $shortUrl = $shortenedUrl ? $shortenedUrl->short_url : $postUrl;

            // Format content for sharing
            $formattedContent = $post->formatForSharing($shortUrl);

            // Get the Nyasajob Career International project (ID: 18)
            $project = $this->getOrCreateDefaultProject();

            // Get user-specific list (Weston: 99, Nathan: 101)
            $projectList = $this->getOrCreateSocialMediaList($project, $user->id);

            // Create task
            $task = Task::create([
                'title' => "Share Job: {$post->title}",
                'description' => $formattedContent,
                'project_id' => $project->id,
                'project_list_id' => $projectList->id,
                'start_at' => now(),
                'due_at' => now()->addHours(24),
            ]);

            // Attach task to user
            $task->users()->attach($user->id);

            // Create job share record
            $jobShare = JobShare::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'v11_post_id' => $post->id,
                'post_title' => $post->title,
                'post_url' => $postUrl,
                'country_code' => $post->country_code,
                'shortened_url' => $shortUrl,
                'formatted_content' => $formattedContent,
                'assigned_at' => now(),
            ]);

            // Send notification
            try {
                $user->notify(new NewJobAssigned($jobShare, $task));
            } catch (\Exception $e) {
                Log::error('Failed to send job assignment notification', [
                    'user_id' => $user->id,
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Job assigned to user', [
                'user_id' => $user->id,
                'post_id' => $post->id,
                'task_id' => $task->id,
            ]);

            return $jobShare;
        });
    }

    /**
     * Get the Nyasajob Career International project.
     *
     * @return Project
     */
    protected function getOrCreateDefaultProject()
    {
        // Use the existing Nyasajob Career International project (ID: 18)
        return Project::find(18);
    }

    /**
     * Get the user-specific project list.
     * Weston (user_id 7) -> List 99 (Weston Tasks)
     * Nathan (user_id 4) -> List 101 (Nathan Tasks)
     *
     * @param  Project  $project
     * @param  int|null  $userId
     * @return ProjectList
     */
    protected function getOrCreateSocialMediaList(Project $project, $userId = null)
    {
        // Map user IDs to their specific lists
        $userListMap = [
            7 => 99,  // Weston -> Weston Tasks
            4 => 101, // Nathan -> Nathan Tasks
        ];

        // Get the list ID for this user, default to Nathan's list
        $listId = $userListMap[$userId] ?? 101;

        return ProjectList::find($listId);
    }

    /**
     * Mark a job as copied by the user.
     *
     * @param  int  $jobShareId
     * @param  int  $userId
     * @return bool
     */
    public function markJobAsCopied($jobShareId, $userId)
    {
        $jobShare = JobShare::where('id', $jobShareId)
            ->where('user_id', $userId)
            ->first();

        if (!$jobShare) {
            return false;
        }

        return $jobShare->markAsCopied();
    }

    /**
     * Get job sharing statistics for a user.
     *
     * @param  int  $userId
     * @return array
     */
    public function getUserStats($userId)
    {
        return [
            'total_assigned' => JobShare::forUser($userId)->count(),
            'copied' => JobShare::forUser($userId)->copied()->count(),
            'pending' => JobShare::forUser($userId)->uncopied()->count(),
            'today_assigned' => JobShare::forUser($userId)
                ->whereDate('assigned_at', today())
                ->count(),
        ];
    }

    /**
     * Assign a specific post by ID to all eligible users.
     * Called when a new job is posted on nyasajob.
     *
     * @param  int  $postId
     * @return array Statistics about the assignment
     */
    public function assignPostById($postId)
    {
        $stats = [
            'post_id' => $postId,
            'assignments_created' => 0,
            'tasks_created' => 0,
            'notifications_sent' => 0,
            'errors' => 0,
        ];

        try {
            $post = Post::where('id', $postId)
                ->whereNotNull('email_verified_at')
                ->whereNull('deleted_at')
                ->whereNull('archived_at')
                ->first();

            if (!$post) {
                Log::warning('Post not found or not eligible for assignment', ['post_id' => $postId]);
                $stats['errors']++;
                return $stats;
            }

            $usersWithCountries = $this->getUsersWithCountries();

            if (empty($usersWithCountries)) {
                Log::info('No users with assigned countries found');
                return $stats;
            }

            $assignmentResult = $this->assignPostToUsers($post, $usersWithCountries);
            $stats['assignments_created'] = $assignmentResult['assignments'];
            $stats['tasks_created'] = $assignmentResult['tasks'];
            $stats['notifications_sent'] = $assignmentResult['notifications'];

            Log::info('Post assigned to users', $stats);

            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to assign post by ID', [
                'post_id' => $postId,
                'error' => $e->getMessage(),
            ]);
            $stats['errors']++;
            return $stats;
        }
    }

    /**
     * Get all country codes that have assigned users.
     *
     * @return array
     */
    public function getActiveCountryCodes()
    {
        return UserCountry::select('country_code')
            ->distinct()
            ->pluck('country_code')
            ->toArray();
    }
}
