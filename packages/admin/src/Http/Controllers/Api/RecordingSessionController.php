<?php

namespace Admin\Http\Controllers\Api;

use App\Models\RecordingSession;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class RecordingSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = RecordingSession::query()
            ->with(['serviceProvider', 'subject', 'topic', 'lessonPlan', 'approvedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('serviceProvider', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service_provider_id')) {
            $query->where('service_provider_id', $request->service_provider_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('clock_in', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('clock_in', '<=', $request->to_date);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $sessions = $query->paginate($request->get('per_page', 15));

        return response()->json($sessions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id',
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'nullable|exists:topics,id',
            'lesson_plan_id' => 'nullable|exists:lesson_plans,id',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'draft';

        $session = RecordingSession::create($validated);

        return response()->json([
            'message' => 'Recording session created successfully',
            'data' => $session->load(['serviceProvider', 'subject', 'topic']),
        ], 201);
    }

    public function show(RecordingSession $recordingSession)
    {
        $recordingSession->load([
            'serviceProvider',
            'subject',
            'topic',
            'lessonPlan',
            'approvedBy',
            'resources',
        ]);

        return response()->json([
            'data' => $recordingSession,
        ]);
    }

    public function update(Request $request, RecordingSession $recordingSession)
    {
        $validated = $request->validate([
            'subject_id' => 'sometimes|exists:subjects,id',
            'topic_id' => 'nullable|exists:topics,id',
            'lesson_plan_id' => 'nullable|exists:lesson_plans,id',
            'notes' => 'nullable|string',
            'admin_notes' => 'nullable|string',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'retakes' => 'nullable|integer|min:0',
        ]);

        $recordingSession->update($validated);

        return response()->json([
            'message' => 'Recording session updated successfully',
            'data' => $recordingSession->load(['serviceProvider', 'subject', 'topic']),
        ]);
    }

    public function destroy(RecordingSession $recordingSession)
    {
        // Delete associated video file
        if ($recordingSession->video_file && Storage::exists($recordingSession->video_file)) {
            Storage::delete($recordingSession->video_file);
        }

        $recordingSession->delete();

        return response()->json([
            'message' => 'Recording session deleted successfully',
        ]);
    }

    public function approve(Request $request, RecordingSession $recordingSession)
    {
        $validated = $request->validate([
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'admin_notes' => 'nullable|string',
        ]);

        if (isset($validated['quality_rating'])) {
            $recordingSession->quality_rating = $validated['quality_rating'];
        }
        if (isset($validated['admin_notes'])) {
            $recordingSession->admin_notes = $validated['admin_notes'];
        }

        $recordingSession->approve($request->user());

        return response()->json([
            'message' => 'Recording session approved successfully',
            'data' => $recordingSession->fresh(['serviceProvider', 'subject', 'topic', 'approvedBy']),
        ]);
    }

    public function reject(Request $request, RecordingSession $recordingSession)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $recordingSession->reject($request->user(), $validated['admin_notes']);

        return response()->json([
            'message' => 'Recording session rejected',
            'data' => $recordingSession->fresh(['serviceProvider', 'subject', 'topic', 'approvedBy']),
        ]);
    }

    public function statistics(Request $request)
    {
        $query = RecordingSession::query();

        if ($request->filled('from_date')) {
            $query->whereDate('clock_in', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('clock_in', '<=', $request->to_date);
        }

        $stats = [
            'total_sessions' => (clone $query)->count(),
            'pending_review' => (clone $query)->where('status', 'pending_review')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'total_recording_minutes' => (clone $query)->sum('recording_minutes'),
            'total_work_minutes' => (clone $query)->sum('total_minutes'),
            'average_quality_rating' => round((clone $query)->whereNotNull('quality_rating')->avg('quality_rating'), 2),
        ];

        return response()->json($stats);
    }

    public function pendingReview(Request $request)
    {
        $sessions = RecordingSession::with(['serviceProvider', 'subject', 'topic', 'lessonPlan'])
            ->where('status', 'pending_review')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($sessions);
    }

    public function uploadVideo(Request $request, RecordingSession $recordingSession)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,webm|max:2048000', // 2GB max
        ]);

        // Delete old video if exists
        if ($recordingSession->video_file && Storage::exists($recordingSession->video_file)) {
            Storage::delete($recordingSession->video_file);
        }

        $path = $request->file('video')->store('recordings/' . $recordingSession->service_provider_id, 'public');

        $recordingSession->update([
            'video_file' => $path,
            'video_duration' => null, // Would need ffprobe to get actual duration
        ]);

        return response()->json([
            'message' => 'Video uploaded successfully',
            'data' => $recordingSession->fresh(),
        ]);
    }
}
