<?php

namespace Admin\Http\Controllers\Api;

use App\Models\InactivityReport;
use App\Models\Option;
use App\Models\UserActivityLog;
use App\Models\UserActivitySession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserActivityController extends Controller
{
    /**
     * Inactivity threshold in minutes before triggering a report.
     */
    private const INACTIVITY_THRESHOLD_MINUTES = 10;

    /**
     * Same page threshold in minutes before triggering a report.
     */
    private const SAME_PAGE_THRESHOLD_MINUTES = 15;

    /**
     * Session stale threshold in minutes (for power outage detection).
     */
    private const SESSION_STALE_THRESHOLD_MINUTES = 5;

    /**
     * Start a new activity session.
     */
    public function startSession(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            // Check if tables exist
            if (!\Schema::hasTable('user_activity_sessions')) {
                return response()->json([
                    'error' => 'Database tables not created',
                    'session_id' => null,
                ]);
            }

            $sessionId = $request->input('session_id', uniqid('sess_', true));

            // Check for ungraceful previous sessions (power outage detection)
            $this->detectPowerOutage($user);

            // End any existing active sessions for this user
            UserActivitySession::forUser($user->id)
                ->active()
                ->update([
                    'ended_at' => now(),
                    'graceful_logout' => false,
                ]);

            // Create new session
            $session = UserActivitySession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'started_at' => now(),
                'last_heartbeat_at' => now(),
                'last_page_url' => $request->input('page_url'),
            ]);

            return response()->json([
                'session_id' => $session->session_id,
                'message' => 'Session started',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'session_id' => null,
            ]);
        }
    }

    /**
     * Record activity heartbeat.
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $pageUrl = $request->input('page_url');
        $pageTitle = $request->input('page_title');
        $sessionId = $request->input('session_id');

        // Check if monitoring is enabled
        if (!$this->isMonitoringEnabled()) {
            return response()->json([
                'status' => 'skipped',
                'reason' => 'monitoring_disabled',
            ]);
        }

        // Check if URL is in exception list (company pages)
        if ($this->isExceptionUrl($pageUrl)) {
            return response()->json([
                'status' => 'skipped',
                'reason' => 'exception_url',
            ]);
        }

        // Skip tracking during lunch time
        if ($this->isLunchTime()) {
            return response()->json([
                'status' => 'skipped',
                'reason' => 'lunch_time',
            ]);
        }

        // Check if user is clocked in
        if (!$this->isUserClockedIn($user)) {
            return response()->json([
                'status' => 'skipped',
                'reason' => 'not_clocked_in',
            ]);
        }

        // Update session heartbeat
        $session = UserActivitySession::forUser($user->id)
            ->where('session_id', $sessionId)
            ->active()
            ->first();

        if ($session) {
            $session->heartbeat($pageUrl);
        }

        // Get or create activity log for current page
        $activityLog = UserActivityLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'is_active' => true,
            ],
            [
                'page_url' => $pageUrl,
                'page_title' => $pageTitle,
                'last_activity_at' => now(),
            ]
        );

        // Check for same page inactivity
        $this->checkSamePageInactivity($user, $activityLog, $pageUrl, $pageTitle);

        return response()->json([
            'status' => 'recorded',
            'last_activity' => $activityLog->last_activity_at,
        ]);
    }

    /**
     * Report user returning after being away.
     */
    public function reportReturn(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Check if monitoring is enabled
        if (!$this->isMonitoringEnabled()) {
            return response()->json([
                'status' => 'skipped',
                'reason' => 'monitoring_disabled',
            ]);
        }

        $awayFrom = Carbon::parse($request->input('away_from'));
        $awayUntil = now();
        $pageUrl = $request->input('page_url');
        $pageTitle = $request->input('page_title');

        // Check if URL is in exception list (company pages)
        if ($this->isExceptionUrl($pageUrl)) {
            return response()->json([
                'status' => 'skipped',
                'reason' => 'exception_url',
            ]);
        }

        // Skip if during lunch time
        if ($this->wasEntirelyDuringLunch($awayFrom, $awayUntil)) {
            return response()->json([
                'status' => 'skipped',
                'reason' => 'lunch_time',
            ]);
        }

        // Calculate actual inactive duration (excluding lunch)
        $inactiveDuration = $this->calculateInactiveDuration($awayFrom, $awayUntil);

        // Only create report if inactive for more than threshold
        if ($inactiveDuration >= self::INACTIVITY_THRESHOLD_MINUTES) {
            $report = InactivityReport::create([
                'user_id' => $user->id,
                'inactive_from' => $awayFrom,
                'inactive_until' => $awayUntil,
                'detected_at' => now(),
                'reason_type' => 'computer_inactive',
                'page_url' => $pageUrl,
                'page_title' => $pageTitle,
                'inactive_duration_minutes' => $inactiveDuration,
                'is_pending' => true,
            ]);

            return response()->json([
                'status' => 'report_created',
                'report_id' => $report->id,
            ]);
        }

        return response()->json([
            'status' => 'below_threshold',
        ]);
    }

    /**
     * Get pending inactivity reports for current user.
     */
    public function getPendingReports(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $reports = InactivityReport::forUser($user->id)
            ->pending()
            ->orderBy('detected_at', 'desc')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'inactive_from' => $report->inactive_from->format('g:i A'),
                    'inactive_until' => $report->inactive_until?->format('g:i A') ?? 'Now',
                    'inactive_from_full' => $report->inactive_from->format('M d, Y g:i A'),
                    'inactive_until_full' => $report->inactive_until?->format('M d, Y g:i A') ?? 'Now',
                    'duration' => $report->formatted_duration,
                    'duration_minutes' => $report->inactive_duration_minutes,
                    'reason_type' => $report->reason_type,
                    'reason_label' => $report->reason_label,
                    'page_url' => $report->page_url,
                    'page_title' => $report->page_title,
                    'detected_at' => $report->detected_at->diffForHumans(),
                ];
            });

        return response()->json([
            'reports' => $reports,
            'has_pending' => $reports->isNotEmpty(),
        ]);
    }

    /**
     * Submit explanation for an inactivity report.
     */
    public function submitExplanation(Request $request, int $reportId): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'explanation' => 'required|string|min:10|max:1000',
        ]);

        $report = InactivityReport::forUser($user->id)
            ->pending()
            ->findOrFail($reportId);

        $report->acknowledge($request->input('explanation'));

        return response()->json([
            'status' => 'acknowledged',
            'message' => 'Thank you for your explanation.',
        ]);
    }

    /**
     * End session gracefully (on logout or page unload).
     */
    public function endSession(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $sessionId = $request->input('session_id');

        $session = UserActivitySession::forUser($user->id)
            ->where('session_id', $sessionId)
            ->active()
            ->first();

        if ($session) {
            $session->endGracefully();
        }

        // Mark activity logs as inactive
        UserActivityLog::forUser($user->id)
            ->where('session_id', $sessionId)
            ->update(['is_active' => false]);

        return response()->json([
            'status' => 'ended',
            'message' => 'Session ended gracefully',
        ]);
    }

    /**
     * Get activity statistics for admins.
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            // Check if tables exist first
            if (!\Schema::hasTable('inactivity_reports')) {
                return response()->json([
                    'error' => 'Database tables not created. Please run: php artisan migrate',
                    'total_reports_today' => 0,
                    'pending_reports' => 0,
                    'acknowledged_today' => 0,
                    'power_outages_today' => 0,
                ]);
            }

            $today = Carbon::today();

            $stats = [
                'total_reports_today' => InactivityReport::whereDate('detected_at', $today)->count(),
                'pending_reports' => InactivityReport::pending()->count(),
                'acknowledged_today' => InactivityReport::whereDate('acknowledged_at', $today)->count(),
                'power_outages_today' => InactivityReport::byType('power_outage')
                    ->whereDate('detected_at', $today)
                    ->count(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'total_reports_today' => 0,
                'pending_reports' => 0,
                'acknowledged_today' => 0,
                'power_outages_today' => 0,
            ]);
        }
    }

    /**
     * Get all inactivity reports for admin view.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Check if tables exist
            if (!\Schema::hasTable('inactivity_reports')) {
                return response()->json([
                    'data' => [],
                    'error' => 'Database tables not created. Please run: php artisan migrate',
                ]);
            }

            $query = InactivityReport::with('user')
                ->orderBy('detected_at', 'desc');

            // Filter by user
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            // Filter by status
            if ($request->filled('status')) {
                if ($request->input('status') === 'pending') {
                    $query->where('is_pending', true);
                } elseif ($request->input('status') === 'acknowledged') {
                    $query->where('is_pending', false);
                }
            }

            // Filter by reason type
            if ($request->filled('reason_type')) {
                $query->where('reason_type', $request->input('reason_type'));
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('detected_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('detected_at', '<=', $request->input('date_to'));
            }

            $reports = $query->paginate($request->input('per_page', 15));

            return response()->json($reports);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get users for filter dropdown.
     */
    public function users(): JsonResponse
    {
        $users = \App\Models\User::select('id', 'name', 'email')
            ->whereHas('attendances')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    /**
     * Update an inactivity report (admin only).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $report = InactivityReport::findOrFail($id);

            $request->validate([
                'user_explanation' => 'nullable|string|max:1000',
            ]);

            $report->update([
                'user_explanation' => $request->input('user_explanation'),
                'is_pending' => empty($request->input('user_explanation')),
                'acknowledged_at' => $request->filled('user_explanation') ? now() : null,
            ]);

            return response()->json([
                'status' => 'updated',
                'message' => 'Report updated successfully.',
                'report' => $report->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an inactivity report (admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $report = InactivityReport::findOrFail($id);
            $report->delete();

            return response()->json([
                'status' => 'deleted',
                'message' => 'Report deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get activity monitoring settings for frontend.
     */
    public function getSettings(): JsonResponse
    {
        $user = request()->user();
        $settings = $this->getActivitySettings();

        // Add clock-in status for the current user
        $settings['is_clocked_in'] = $user ? $this->isUserClockedIn($user) : false;

        return response()->json($settings);
    }

    /**
     * Save activity monitoring settings (admin only).
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'exception_urls' => 'nullable|array',
            'exception_urls.*' => 'nullable|string|url',
            'same_page_threshold' => 'required|integer|min:5|max:120',
            'inactivity_threshold' => 'required|integer|min:5|max:120',
            'heartbeat_interval' => 'required|integer|min:30|max:300',
            'lunch_start' => 'required|string',
            'lunch_end' => 'required|string',
        ]);

        $option = new Option();
        $option->set('activity_monitoring_settings', [
            'enabled' => $request->input('enabled'),
            'exception_urls' => array_filter($request->input('exception_urls', [])),
            'same_page_threshold' => $request->input('same_page_threshold'),
            'inactivity_threshold' => $request->input('inactivity_threshold'),
            'heartbeat_interval' => $request->input('heartbeat_interval'),
            'lunch_start' => $request->input('lunch_start'),
            'lunch_end' => $request->input('lunch_end'),
        ]);

        return response()->json([
            'status' => 'saved',
            'message' => 'Settings saved successfully.',
            'settings' => $this->getActivitySettings(),
        ]);
    }

    /**
     * Get activity settings from options.
     */
    private function getActivitySettings(): array
    {
        $option = new Option();
        $settings = $option->get('activity_monitoring_settings', []);

        // Default settings
        $defaults = [
            'enabled' => true,
            'exception_urls' => [
                'https://nyasajob.com',
                'https://studyseco.com',
                'https://malawirents.com',
                'https://emphxs.com',
                'https://zoswa.com',
            ],
            'same_page_threshold' => 15,
            'inactivity_threshold' => 10,
            'heartbeat_interval' => 60,
            'lunch_start' => '12:00',
            'lunch_end' => '13:00',
        ];

        return array_merge($defaults, $settings);
    }

    /**
     * Check if a URL matches any exception URL.
     */
    private function isExceptionUrl(?string $pageUrl): bool
    {
        if (!$pageUrl) {
            return false;
        }

        $settings = $this->getActivitySettings();
        $exceptionUrls = $settings['exception_urls'] ?? [];

        foreach ($exceptionUrls as $exceptionUrl) {
            // Extract domain from exception URL
            $exceptionDomain = parse_url($exceptionUrl, PHP_URL_HOST);
            $pageDomain = parse_url($pageUrl, PHP_URL_HOST);

            if ($exceptionDomain && $pageDomain) {
                // Remove www. prefix for comparison
                $exceptionDomain = preg_replace('/^www\./', '', $exceptionDomain);
                $pageDomain = preg_replace('/^www\./', '', $pageDomain);

                if ($exceptionDomain === $pageDomain) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if activity monitoring is enabled.
     */
    private function isMonitoringEnabled(): bool
    {
        $settings = $this->getActivitySettings();
        return $settings['enabled'] ?? true;
    }

    /**
     * Check if current time is during lunch.
     */
    private function isLunchTime(): bool
    {
        $settings = $this->getActivitySettings();
        $lunchStartTime = $settings['lunch_start'] ?? '12:00';
        $lunchEndTime = $settings['lunch_end'] ?? '13:00';

        $now = now();
        [$startHour, $startMinute] = explode(':', $lunchStartTime);
        [$endHour, $endMinute] = explode(':', $lunchEndTime);

        $lunchStart = $now->copy()->setTime((int)$startHour, (int)$startMinute, 0);
        $lunchEnd = $now->copy()->setTime((int)$endHour, (int)$endMinute, 0);

        return $now->between($lunchStart, $lunchEnd);
    }

    /**
     * Check if the entire away period was during lunch.
     */
    private function wasEntirelyDuringLunch(Carbon $from, Carbon $until): bool
    {
        $lunchStart = $from->copy()->setTime(12, 0, 0);
        $lunchEnd = $from->copy()->setTime(13, 0, 0);

        return $from->gte($lunchStart) && $until->lte($lunchEnd);
    }

    /**
     * Calculate inactive duration excluding lunch time.
     */
    private function calculateInactiveDuration(Carbon $from, Carbon $until): int
    {
        $totalMinutes = $from->diffInMinutes($until);

        // Check if lunch time overlaps with inactive period
        $lunchStart = $from->copy()->setTime(12, 0, 0);
        $lunchEnd = $from->copy()->setTime(13, 0, 0);

        // If both dates are on the same day
        if ($from->isSameDay($until)) {
            // Calculate lunch overlap
            $overlapStart = max($from->timestamp, $lunchStart->timestamp);
            $overlapEnd = min($until->timestamp, $lunchEnd->timestamp);

            if ($overlapStart < $overlapEnd) {
                $lunchOverlap = ($overlapEnd - $overlapStart) / 60;
                $totalMinutes -= $lunchOverlap;
            }
        }

        return max(0, (int) $totalMinutes);
    }

    /**
     * Check if user is clocked in.
     */
    private function isUserClockedIn($user): bool
    {
        // Check if there's an attendance record for today without clock out
        return $user->attendances()
            ->whereDate('clock_in', today())
            ->whereNull('clock_out')
            ->exists();
    }

    /**
     * Check for same page inactivity.
     */
    private function checkSamePageInactivity($user, $activityLog, $pageUrl, $pageTitle): void
    {
        // Get the oldest activity on the same page
        $oldestOnPage = UserActivityLog::forUser($user->id)
            ->where('page_url', $pageUrl)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->first();

        if ($oldestOnPage) {
            $minutesOnPage = $oldestOnPage->created_at->diffInMinutes(now());

            // Check if user has been on same page for too long
            if ($minutesOnPage >= self::SAME_PAGE_THRESHOLD_MINUTES) {
                // Check if we already have a pending report for this
                $existingReport = InactivityReport::forUser($user->id)
                    ->where('page_url', $pageUrl)
                    ->where('reason_type', 'same_page')
                    ->pending()
                    ->first();

                if (!$existingReport) {
                    InactivityReport::create([
                        'user_id' => $user->id,
                        'inactive_from' => $oldestOnPage->created_at,
                        'inactive_until' => now(),
                        'detected_at' => now(),
                        'reason_type' => 'same_page',
                        'page_url' => $pageUrl,
                        'page_title' => $pageTitle,
                        'inactive_duration_minutes' => $minutesOnPage,
                        'is_pending' => true,
                    ]);

                    // Reset the activity log timestamp
                    $oldestOnPage->update(['created_at' => now()]);
                }
            }
        }
    }

    /**
     * Detect power outage from previous session.
     */
    private function detectPowerOutage($user): void
    {
        // Find the last session that wasn't ended gracefully
        $lastSession = UserActivitySession::forUser($user->id)
            ->whereNotNull('ended_at')
            ->where('graceful_logout', false)
            ->orderBy('ended_at', 'desc')
            ->first();

        if (!$lastSession) {
            // Check for sessions that should have ended but didn't
            $staleSession = UserActivitySession::forUser($user->id)
                ->active()
                ->where('last_heartbeat_at', '<', now()->subMinutes(self::SESSION_STALE_THRESHOLD_MINUTES))
                ->first();

            if ($staleSession) {
                $staleSession->endUngracefully();
                $lastSession = $staleSession;
            }
        }

        if ($lastSession && !$lastSession->graceful_logout) {
            $inactiveDuration = $lastSession->last_heartbeat_at->diffInMinutes(now());

            // Only report if gap is significant (more than threshold)
            if ($inactiveDuration >= self::INACTIVITY_THRESHOLD_MINUTES) {
                // Check if we already have a pending report for this session
                $existingReport = InactivityReport::forUser($user->id)
                    ->where('inactive_from', $lastSession->last_heartbeat_at)
                    ->where('reason_type', 'power_outage')
                    ->first();

                if (!$existingReport) {
                    InactivityReport::create([
                        'user_id' => $user->id,
                        'inactive_from' => $lastSession->last_heartbeat_at,
                        'inactive_until' => now(),
                        'detected_at' => now(),
                        'reason_type' => 'power_outage',
                        'page_url' => $lastSession->last_page_url,
                        'inactive_duration_minutes' => $inactiveDuration,
                        'is_pending' => true,
                    ]);
                }
            }
        }
    }
}
