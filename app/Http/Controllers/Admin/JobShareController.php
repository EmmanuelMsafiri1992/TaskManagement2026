<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobShare;
use App\Services\JobAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobShareController extends Controller
{
    /**
     * @var JobAssignmentService
     */
    protected $jobAssignmentService;

    /**
     * Constructor.
     *
     * @param  JobAssignmentService  $jobAssignmentService
     */
    public function __construct(JobAssignmentService $jobAssignmentService)
    {
        $this->jobAssignmentService = $jobAssignmentService;
    }

    /**
     * Get all job shares for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = JobShare::forUser(Auth::id())
            ->with('task')
            ->orderBy('assigned_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'copied') {
                $query->copied();
            } elseif ($request->status === 'pending') {
                $query->uncopied();
            }
        }

        // Filter by country
        if ($request->has('country_code')) {
            $query->forCountry($request->country_code);
        }

        $jobShares = $query->paginate($request->get('per_page', 20));

        return response()->json($jobShares);
    }

    /**
     * Get a specific job share.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $jobShare = JobShare::forUser(Auth::id())
            ->with('task')
            ->findOrFail($id);

        return response()->json($jobShare);
    }

    /**
     * Mark a job as copied.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsCopied(Request $request, $id)
    {
        $success = $this->jobAssignmentService->markJobAsCopied($id, Auth::id());

        if (!$success) {
            return response()->json([
                'message' => 'Job share not found or does not belong to you',
            ], 404);
        }

        return response()->json([
            'message' => 'Job marked as copied successfully',
        ]);
    }

    /**
     * Get job sharing statistics for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = $this->jobAssignmentService->getUserStats(Auth::id());

        return response()->json($stats);
    }

    /**
     * Manually trigger job assignment (admin only).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function triggerAssignment()
    {
        // Check if user has admin permissions
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $stats = $this->jobAssignmentService->assignNewJobs();

            return response()->json([
                'message' => 'Job assignment completed successfully',
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Job assignment failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
