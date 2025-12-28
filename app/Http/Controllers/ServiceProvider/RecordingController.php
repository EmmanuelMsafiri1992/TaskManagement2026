<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\RecordingSession;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecordingController extends Controller
{
    public function index(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $query = $provider->recordingSessions()
            ->with(['subject', 'topic', 'lessonPlan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $sessions = $query->orderBy('created_at', 'desc')->paginate(15);

        $subjects = Subject::where('is_active', true)
            ->orderBy('form')
            ->orderBy('sort_order')
            ->get();

        return view('service-provider.recordings.index', compact('sessions', 'subjects'));
    }

    public function create()
    {
        $provider = Auth::guard('service_provider')->user();

        $subjects = Subject::where('is_active', true)
            ->orderBy('form')
            ->orderBy('sort_order')
            ->get();

        $lessonPlans = $provider->lessonPlans()
            ->where('status', 'approved')
            ->with('topic.subject')
            ->get();

        return view('service-provider.recordings.create', compact('subjects', 'lessonPlans'));
    }

    public function store(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'nullable|exists:topics,id',
            'lesson_plan_id' => 'nullable|exists:lesson_plans,id',
            'notes' => 'nullable|string',
        ]);

        $validated['service_provider_id'] = $provider->id;
        $validated['status'] = 'scheduled';

        $session = RecordingSession::create($validated);

        return redirect()->route('service-provider.recordings.show', $session)
            ->with('success', 'Recording session created. You can now clock in.');
    }

    public function show(RecordingSession $session)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($session->service_provider_id !== $provider->id) {
            abort(403);
        }

        $session->load(['subject', 'topic', 'lessonPlan', 'resources']);

        return view('service-provider.recordings.show', compact('session'));
    }

    public function clockIn(RecordingSession $session)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($session->service_provider_id !== $provider->id) {
            abort(403);
        }

        if ($session->clock_in) {
            return redirect()->back()->with('error', 'Already clocked in');
        }

        $session->clockIn();

        return redirect()->back()->with('success', 'Clocked in successfully');
    }

    public function clockOut(RecordingSession $session)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($session->service_provider_id !== $provider->id) {
            abort(403);
        }

        if (!$session->clock_in) {
            return redirect()->back()->with('error', 'You need to clock in first');
        }

        if ($session->clock_out) {
            return redirect()->back()->with('error', 'Already clocked out');
        }

        $session->clockOut();

        return redirect()->back()->with('success', 'Clocked out successfully. Session completed.');
    }

    public function startRecording(RecordingSession $session)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($session->service_provider_id !== $provider->id) {
            abort(403);
        }

        if (!$session->clock_in) {
            return redirect()->back()->with('error', 'You need to clock in first');
        }

        if ($session->recording_start) {
            return redirect()->back()->with('error', 'Recording already started');
        }

        $session->startRecording();

        return redirect()->back()->with('success', 'Recording started');
    }

    public function stopRecording(RecordingSession $session)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($session->service_provider_id !== $provider->id) {
            abort(403);
        }

        if (!$session->recording_start) {
            return redirect()->back()->with('error', 'Recording not started');
        }

        $session->stopRecording();

        return redirect()->back()->with('success', 'Recording stopped');
    }

    public function submit(RecordingSession $session)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($session->service_provider_id !== $provider->id) {
            abort(403);
        }

        if ($session->status !== 'completed') {
            return redirect()->back()->with('error', 'Please complete the session first');
        }

        $session->update(['status' => 'pending_review']);

        return redirect()->back()->with('success', 'Session submitted for review');
    }

    public function uploadVideo(Request $request, RecordingSession $session)
    {
        $provider = Auth::guard('service_provider')->user();

        if ($session->service_provider_id !== $provider->id) {
            abort(403);
        }

        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,webm|max:2048000',
        ]);

        // Delete old video if exists
        if ($session->video_file && Storage::disk('public')->exists($session->video_file)) {
            Storage::disk('public')->delete($session->video_file);
        }

        $path = $request->file('video')->store('recordings/' . $provider->id, 'public');

        $session->update(['video_file' => $path]);

        return redirect()->back()->with('success', 'Video uploaded successfully');
    }
}
