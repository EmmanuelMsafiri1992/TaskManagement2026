<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\JobAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JobWebhookController extends Controller
{
    /**
     * @var JobAssignmentService
     */
    protected $jobAssignmentService;

    /**
     * Create a new controller instance.
     *
     * @param  JobAssignmentService  $jobAssignmentService
     */
    public function __construct(JobAssignmentService $jobAssignmentService)
    {
        $this->jobAssignmentService = $jobAssignmentService;
    }

    /**
     * Handle webhook when a new job is posted on nyasajob.
     * This endpoint is called by nyasajob's PostObserver when a job is verified.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function jobPosted(Request $request)
    {
        // Validate the API token
        $token = $request->header('X-Api-Token') ?? $request->input('api_token');
        $expectedToken = config('services.nyasajob.webhook_token');

        if ($expectedToken && $token !== $expectedToken) {
            Log::warning('Job webhook called with invalid token');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate request
        $validated = $request->validate([
            'post_id' => 'required|integer',
            'country_code' => 'sometimes|string|size:2',
            'title' => 'sometimes|string',
        ]);

        $postId = $validated['post_id'];

        Log::info('Job webhook received', [
            'post_id' => $postId,
            'country_code' => $validated['country_code'] ?? null,
            'title' => $validated['title'] ?? null,
        ]);

        try {
            // Assign the job to eligible users
            $stats = $this->jobAssignmentService->assignPostById($postId);

            return response()->json([
                'success' => true,
                'message' => "Job #{$postId} processed",
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Job webhook processing failed', [
                'post_id' => $postId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process job',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of active country codes that have users assigned.
     * Nyasajob can use this to know which countries to trigger webhooks for.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveCountries(Request $request)
    {
        // Validate the API token
        $token = $request->header('X-Api-Token') ?? $request->input('api_token');
        $expectedToken = config('services.nyasajob.webhook_token');

        if ($expectedToken && $token !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $countries = $this->jobAssignmentService->getActiveCountryCodes();

        return response()->json([
            'success' => true,
            'countries' => $countries,
            'count' => count($countries),
        ]);
    }

    /**
     * Health check endpoint.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'job-assignment',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
