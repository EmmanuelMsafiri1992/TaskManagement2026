<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $provider = Auth::guard('service_provider')->user();

        $stats = [
            'total_sessions' => $provider->recordingSessions()->count(),
            'approved_sessions' => $provider->recordingSessions()->where('status', 'approved')->count(),
            'pending_sessions' => $provider->recordingSessions()->whereIn('status', ['pending_review', 'completed'])->count(),
            'total_recording_hours' => round($provider->recordingSessions()->sum('recording_minutes') / 60, 2),
            'total_work_hours' => round($provider->recordingSessions()->sum('total_minutes') / 60, 2),
            'total_lesson_plans' => $provider->lessonPlans()->count(),
            'approved_lesson_plans' => $provider->lessonPlans()->where('status', 'approved')->count(),
        ];

        $recentSessions = $provider->recordingSessions()
            ->with(['subject', 'topic'])
            ->latest()
            ->limit(5)
            ->get();

        $recentPlans = $provider->lessonPlans()
            ->with('topic.subject')
            ->latest()
            ->limit(5)
            ->get();

        return view('service-provider.dashboard', compact('provider', 'stats', 'recentSessions', 'recentPlans'));
    }

    public function profile()
    {
        $provider = Auth::guard('service_provider')->user();

        // Get distinct subject names with total topics count across all forms
        $subjectNames = Subject::where('is_active', true)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        // Calculate total topics for each subject name across all forms
        $subjects = $subjectNames->map(function ($name) {
            $totalTopics = Topic::whereHas('subject', function ($query) use ($name) {
                $query->where('name', $name)->where('is_active', true);
            })->where('is_active', true)->count();

            return [
                'name' => $name,
                'total_topics' => $totalTopics,
                'forms' => '1-4',
            ];
        });

        return view('service-provider.profile', compact('provider', 'subjects'));
    }

    public function updateProfile(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'specialty' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
        ]);

        $provider->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function updatePaymentSettings(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $validated = $request->validate([
            'payment_preference' => 'required|in:bi_weekly,monthly,lump_sum,daily',
            'monthly_amount' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:1000',
        ]);

        // If daily preference is selected, ensure daily_rate is provided
        if ($validated['payment_preference'] === 'daily' && empty($validated['daily_rate'])) {
            return redirect()->back()->withErrors(['daily_rate' => 'Daily rate is required when selecting daily payment preference.']);
        }

        // Clear daily_rate if not using daily preference
        if ($validated['payment_preference'] !== 'daily') {
            $validated['daily_rate'] = null;
        }

        // Clear monthly_amount if using daily or lump_sum preference
        if (in_array($validated['payment_preference'], ['daily', 'lump_sum'])) {
            $validated['monthly_amount'] = null;
        }

        $provider->update($validated);

        $message = 'Payment preferences updated successfully';

        // Add info about daily rate requirements
        if ($validated['payment_preference'] === 'daily' && $validated['daily_rate']) {
            $maxDays = $provider->getMaxPayableDays();
            $topicsPerDay = $provider->getRequiredTopicsPerDay();
            $message .= ". With your daily rate of MK " . number_format($validated['daily_rate'], 2) .
                ", you have {$maxDays} payable days and must complete at least {$topicsPerDay} topics per day.";
        }

        return redirect()->back()->with('success', $message);
    }

    public function saveDailyPaymentSetup(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $validated = $request->validate([
            'daily_subject_name' => 'required|string|max:255',
            'daily_total_topics' => 'required|integer|min:1',
            'assigned_subjects_count' => 'required|integer|in:1,2',
        ]);

        // Verify the subject name exists
        $subjectExists = Subject::where('name', $validated['daily_subject_name'])
            ->where('is_active', true)
            ->exists();

        if (!$subjectExists) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid subject name selected'
            ], 422);
        }

        // Calculate amount based on number of subjects (350k per subject)
        $amountPerSubject = 350000;
        $totalAmount = $amountPerSubject * $validated['assigned_subjects_count'];

        $provider->update([
            'daily_subject_name' => $validated['daily_subject_name'],
            'daily_total_topics' => $validated['daily_total_topics'],
            'assigned_subjects_count' => $validated['assigned_subjects_count'],
            'amount_per_subject' => $amountPerSubject,
            'total_agreed_amount' => $totalAmount,
            'daily_payment_setup_complete' => true,
        ]);

        // Calculate topics per day
        $dailyRate = $provider->daily_rate ?? 40000;
        $maxDays = $dailyRate > 0 ? floor($totalAmount / $dailyRate) : 0;
        $topicsPerDay = $maxDays > 0 ? ceil($validated['daily_total_topics'] / $maxDays) : 0;

        return response()->json([
            'success' => true,
            'message' => 'Daily payment setup saved successfully',
            'data' => [
                'subject_name' => $validated['daily_subject_name'],
                'total_amount' => $totalAmount,
                'amount_per_subject' => $amountPerSubject,
                'max_days' => $maxDays,
                'topics_per_day' => $topicsPerDay,
                'daily_rate' => $dailyRate,
            ]
        ]);
    }

    public function updatePaymentMethod(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $validated = $request->validate([
            'payment_method' => 'required|in:bank,mobile_money',
            'bank_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'mobile_money_provider' => 'nullable|string|max:255',
            'mobile_money_number' => 'nullable|string|max:255',
            'mobile_money_name' => 'nullable|string|max:255',
        ]);

        $provider->update($validated);

        return redirect()->back()->with('success', 'Payment method updated successfully');
    }

    public function downloadAgreement()
    {
        $provider = Auth::guard('service_provider')->user();

        if (!$provider->agreement_signed) {
            return redirect()->back()->with('error', 'You have not signed an agreement yet.');
        }

        $data = [
            'provider' => $provider,
            'company' => [
                'name' => 'Studyseco.com',
                'address' => 'Blantyre, Malawi',
                'phone' => '+265 999 000 000',
                'email' => 'info@studyseco.com',
            ],
            'agreement_number' => 'AGR-' . str_pad($provider->id, 5, '0', STR_PAD_LEFT),
            'effective_date' => $provider->created_at->format('F d, Y'),
            'signed_date' => $provider->agreement_signed_at?->format('F d, Y'),
            'generated_at' => now()->format('F d, Y H:i'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('agreements.service-provider', $data);

        return $pdf->download("my-agreement-{$provider->name}.pdf");
    }

    public function acknowledgeSyllabusCommitment(Request $request)
    {
        $provider = Auth::guard('service_provider')->user();

        $request->validate([
            'acknowledge' => 'required|accepted',
        ], [
            'acknowledge.required' => 'You must check the acknowledgment checkbox.',
            'acknowledge.accepted' => 'You must acknowledge your commitment to complete the entire syllabus.',
        ]);

        $provider->update([
            'syllabus_commitment_acknowledged' => true,
            'syllabus_commitment_acknowledged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Thank you for acknowledging your commitment to complete the entire syllabus for each assigned subject.');
    }

    public function payments()
    {
        $provider = Auth::guard('service_provider')->user();

        $payments = $provider->payments()
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('service-provider.payments', compact('provider', 'payments'));
    }

    public function subjects()
    {
        $subjects = Subject::where('is_active', true)
            ->orderBy('form')
            ->orderBy('sort_order')
            ->get();

        return response()->json($subjects);
    }

    public function topics(Subject $subject)
    {
        $topics = $subject->topics()
            ->where('is_active', true)
            ->orderBy('term')
            ->orderBy('week')
            ->get();

        return response()->json($topics);
    }

    public function statistics()
    {
        $provider = Auth::guard('service_provider')->user();

        $stats = [
            'total_sessions' => $provider->recordingSessions()->count(),
            'approved_sessions' => $provider->recordingSessions()->where('status', 'approved')->count(),
            'pending_sessions' => $provider->recordingSessions()->whereIn('status', ['pending_review', 'completed'])->count(),
            'rejected_sessions' => $provider->recordingSessions()->where('status', 'rejected')->count(),
            'total_recording_minutes' => $provider->recordingSessions()->sum('recording_minutes'),
            'total_work_minutes' => $provider->recordingSessions()->sum('total_minutes'),
            'average_quality' => round($provider->recordingSessions()->whereNotNull('quality_rating')->avg('quality_rating'), 2),
            'total_lesson_plans' => $provider->lessonPlans()->count(),
            'approved_lesson_plans' => $provider->lessonPlans()->where('status', 'approved')->count(),
            'pending_lesson_plans' => $provider->lessonPlans()->where('status', 'submitted')->count(),
        ];

        return response()->json($stats);
    }
}
