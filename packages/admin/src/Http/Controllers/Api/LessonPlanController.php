<?php

namespace Admin\Http\Controllers\Api;

use App\Models\LessonPlan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LessonPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = LessonPlan::query()
            ->with(['topic.subject', 'serviceProvider', 'approvedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('serviceProvider', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('service_provider_id')) {
            $query->where('service_provider_id', $request->service_provider_id);
        }

        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $plans = $query->paginate($request->get('per_page', 15));

        return response()->json($plans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'service_provider_id' => 'required|exists:service_providers,id',
            'title' => 'required|string|max:255',
            'objectives' => 'nullable|string',
            'introduction' => 'nullable|string',
            'main_content' => 'nullable|string',
            'activities' => 'nullable|string',
            'assessment' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'homework' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);

        $validated['status'] = 'draft';

        $plan = LessonPlan::create($validated);

        return response()->json([
            'message' => 'Lesson plan created successfully',
            'data' => $plan->load(['topic.subject', 'serviceProvider']),
        ], 201);
    }

    public function show(LessonPlan $lessonPlan)
    {
        $lessonPlan->load([
            'topic.subject',
            'serviceProvider',
            'approvedBy',
            'recordingSessions',
        ]);

        return response()->json([
            'data' => $lessonPlan,
        ]);
    }

    public function update(Request $request, LessonPlan $lessonPlan)
    {
        $validated = $request->validate([
            'topic_id' => 'sometimes|exists:topics,id',
            'title' => 'sometimes|required|string|max:255',
            'objectives' => 'nullable|string',
            'introduction' => 'nullable|string',
            'main_content' => 'nullable|string',
            'activities' => 'nullable|string',
            'assessment' => 'nullable|string',
            'conclusion' => 'nullable|string',
            'homework' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);

        $lessonPlan->update($validated);

        return response()->json([
            'message' => 'Lesson plan updated successfully',
            'data' => $lessonPlan->load(['topic.subject', 'serviceProvider']),
        ]);
    }

    public function destroy(LessonPlan $lessonPlan)
    {
        $lessonPlan->delete();

        return response()->json([
            'message' => 'Lesson plan deleted successfully',
        ]);
    }

    public function approve(Request $request, LessonPlan $lessonPlan)
    {
        $lessonPlan->approve($request->user());

        return response()->json([
            'message' => 'Lesson plan approved successfully',
            'data' => $lessonPlan->fresh(['topic.subject', 'serviceProvider', 'approvedBy']),
        ]);
    }

    public function reject(Request $request, LessonPlan $lessonPlan)
    {
        $validated = $request->validate([
            'feedback' => 'required|string',
        ]);

        $lessonPlan->reject($request->user(), $validated['feedback']);

        return response()->json([
            'message' => 'Lesson plan rejected',
            'data' => $lessonPlan->fresh(['topic.subject', 'serviceProvider', 'approvedBy']),
        ]);
    }

    public function pendingReview(Request $request)
    {
        $plans = LessonPlan::with(['topic.subject', 'serviceProvider'])
            ->where('status', 'submitted')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($plans);
    }

    public function statistics()
    {
        $stats = [
            'total' => LessonPlan::count(),
            'draft' => LessonPlan::where('status', 'draft')->count(),
            'submitted' => LessonPlan::where('status', 'submitted')->count(),
            'approved' => LessonPlan::where('status', 'approved')->count(),
            'rejected' => LessonPlan::where('status', 'rejected')->count(),
        ];

        return response()->json($stats);
    }
}
