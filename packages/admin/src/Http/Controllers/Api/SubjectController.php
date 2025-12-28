<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query()->withCount('topics');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('form')) {
            $query->where('form', $request->form);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $query->orderBy('form')->orderBy('sort_order');

        if ($request->boolean('all')) {
            return response()->json(Subject::orderBy('form')->orderBy('sort_order')->get());
        }

        $subjects = $query->paginate($request->get('per_page', 15));

        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subjects,code',
            'form' => 'required|string|in:1,2,3,4',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $subject = Subject::create($validated);

        return response()->json([
            'message' => 'Subject created successfully',
            'data' => $subject,
        ], 201);
    }

    public function show(Subject $subject)
    {
        $subject->load('topics');
        $subject->loadCount(['topics', 'recordingSessions']);

        return response()->json([
            'data' => $subject,
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:20|unique:subjects,code,' . $subject->id,
            'form' => 'sometimes|required|string|in:1,2,3,4',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $subject->update($validated);

        return response()->json([
            'message' => 'Subject updated successfully',
            'data' => $subject,
        ]);
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return response()->json([
            'message' => 'Subject deleted successfully',
        ]);
    }

    public function topics(Subject $subject, Request $request)
    {
        $query = $subject->topics();

        if ($request->filled('term')) {
            $query->where('term', $request->term);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $topics = $query->orderBy('term')->orderBy('week')->get();

        return response()->json($topics);
    }

    public function storeTopic(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'term' => 'required|integer|in:1,2,3',
            'week' => 'required|integer|min:1|max:15',
            'description' => 'nullable|string',
            'learning_objectives' => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $topic = $subject->topics()->create($validated);

        return response()->json([
            'message' => 'Topic created successfully',
            'data' => $topic,
        ], 201);
    }

    public function updateTopic(Request $request, Subject $subject, Topic $topic)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'term' => 'sometimes|required|integer|in:1,2,3',
            'week' => 'sometimes|required|integer|min:1|max:15',
            'description' => 'nullable|string',
            'learning_objectives' => 'nullable|string',
            'estimated_hours' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $topic->update($validated);

        return response()->json([
            'message' => 'Topic updated successfully',
            'data' => $topic,
        ]);
    }

    public function destroyTopic(Subject $subject, Topic $topic)
    {
        $topic->delete();

        return response()->json([
            'message' => 'Topic deleted successfully',
        ]);
    }

    public function byForm(Request $request)
    {
        $subjects = Subject::where('is_active', true)
            ->orderBy('form')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('form');

        return response()->json($subjects);
    }

    public function statistics()
    {
        $stats = [
            'total_subjects' => Subject::count(),
            'active_subjects' => Subject::where('is_active', true)->count(),
            'total_topics' => Topic::count(),
            'subjects_by_form' => [
                'form_1' => Subject::where('form', '1')->count(),
                'form_2' => Subject::where('form', '2')->count(),
                'form_3' => Subject::where('form', '3')->count(),
                'form_4' => Subject::where('form', '4')->count(),
            ],
        ];

        return response()->json($stats);
    }
}
