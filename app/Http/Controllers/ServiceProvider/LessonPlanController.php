<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonPlanController extends Controller
{
    public function index(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $query = $provider->lessonPlans()
            ->with(['topic.subject', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $plans = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('service-provider.lesson-plans.index', compact('plans'));
    }

    public function create()
    {
        $subjects = Subject::where('is_active', true)
            ->with('topics')
            ->orderBy('form')
            ->orderBy('sort_order')
            ->get();

        return view('service-provider.lesson-plans.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
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

        $validated['service_provider_id'] = $provider->id;
        $validated['status'] = 'draft';

        $plan = LessonPlan::create($validated);

        return redirect()->route('service-provider.lesson-plans.show', $plan)
            ->with('success', 'Lesson plan created successfully');
    }

    public function show(LessonPlan $plan)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($plan->service_provider_id !== $provider->id) {
            abort(403);
        }

        $plan->load(['topic.subject', 'approvedBy', 'recordingSessions']);

        return view('service-provider.lesson-plans.show', compact('plan'));
    }

    public function edit(LessonPlan $plan)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($plan->service_provider_id !== $provider->id) {
            abort(403);
        }

        if (!in_array($plan->status, ['draft', 'rejected'])) {
            return redirect()->back()->with('error', 'Cannot edit this lesson plan');
        }

        $subjects = Subject::where('is_active', true)
            ->with('topics')
            ->orderBy('form')
            ->orderBy('sort_order')
            ->get();

        return view('service-provider.lesson-plans.edit', compact('plan', 'subjects'));
    }

    public function update(Request $request, LessonPlan $plan)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($plan->service_provider_id !== $provider->id) {
            abort(403);
        }

        if (!in_array($plan->status, ['draft', 'rejected'])) {
            return redirect()->back()->with('error', 'Cannot edit this lesson plan');
        }

        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
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

        $plan->update($validated);

        return redirect()->route('service-provider.lesson-plans.show', $plan)
            ->with('success', 'Lesson plan updated successfully');
    }

    public function submit(LessonPlan $plan)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($plan->service_provider_id !== $provider->id) {
            abort(403);
        }

        if (!in_array($plan->status, ['draft', 'rejected'])) {
            return redirect()->back()->with('error', 'Cannot submit this lesson plan');
        }

        $plan->submit();

        return redirect()->back()->with('success', 'Lesson plan submitted for review');
    }
}
