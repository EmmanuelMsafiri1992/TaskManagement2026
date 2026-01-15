<?php

namespace Admin\Http\Controllers\Api;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderAgreement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ServiceProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceProvider::query()
            ->with(['agreements' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->withCount(['recordingSessions', 'lessonPlans']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', "%{$request->specialty}%");
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $providers = $query->paginate($request->get('per_page', 15));

        return response()->json($providers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:service_providers,email',
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:50|unique:service_providers,national_id',
            'address' => 'nullable|string|max:500',
            'specialty' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
            'total_agreed_amount' => 'nullable|numeric|min:0',
            'payment_preference' => 'nullable|in:monthly,lump_sum',
            'monthly_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:bank,mobile_money',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'mobile_money_provider' => 'nullable|string|max:255',
            'mobile_money_number' => 'nullable|string|max:255',
            'mobile_money_name' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'pending';

        // Set default total agreed amount if not provided
        if (!isset($validated['total_agreed_amount'])) {
            $validated['total_agreed_amount'] = 700000.00;
        }

        $provider = ServiceProvider::create($validated);

        return response()->json([
            'message' => 'Service provider created successfully',
            'data' => $provider,
        ], 201);
    }

    public function show(ServiceProvider $serviceProvider)
    {
        $serviceProvider->load([
            'agreements',
            'recordingSessions' => function ($q) {
                $q->with(['subject', 'topic'])->latest()->limit(10);
            },
            'lessonPlans' => function ($q) {
                $q->with('topic')->latest()->limit(10);
            },
        ]);

        $serviceProvider->loadCount(['recordingSessions', 'lessonPlans']);

        // Calculate statistics
        $stats = [
            'total_sessions' => $serviceProvider->recordingSessions()->count(),
            'total_recording_minutes' => $serviceProvider->recordingSessions()->sum('recording_minutes'),
            'total_hours_worked' => round($serviceProvider->recordingSessions()->sum('total_minutes') / 60, 2),
            'approved_sessions' => $serviceProvider->recordingSessions()->where('status', 'approved')->count(),
            'pending_sessions' => $serviceProvider->recordingSessions()->whereIn('status', ['pending_review', 'completed'])->count(),
            'approved_lesson_plans' => $serviceProvider->lessonPlans()->where('status', 'approved')->count(),
            'pending_lesson_plans' => $serviceProvider->lessonPlans()->where('status', 'submitted')->count(),
        ];

        return response()->json([
            'data' => $serviceProvider,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, ServiceProvider $serviceProvider)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:service_providers,email,' . $serviceProvider->id,
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string|max:50|unique:service_providers,national_id,' . $serviceProvider->id,
            'address' => 'nullable|string|max:500',
            'specialty' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
            'total_agreed_amount' => 'nullable|numeric|min:0',
            'payment_preference' => 'nullable|in:monthly,lump_sum',
            'monthly_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:bank,mobile_money',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'mobile_money_provider' => 'nullable|string|max:255',
            'mobile_money_number' => 'nullable|string|max:255',
            'mobile_money_name' => 'nullable|string|max:255',
            'status' => 'sometimes|in:pending,active,suspended,terminated',
            'password' => 'nullable|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $serviceProvider->update($validated);

        return response()->json([
            'message' => 'Service provider updated successfully',
            'data' => $serviceProvider,
        ]);
    }

    public function destroy(ServiceProvider $serviceProvider)
    {
        $serviceProvider->delete();

        return response()->json([
            'message' => 'Service provider deleted successfully',
        ]);
    }

    public function activate(ServiceProvider $serviceProvider)
    {
        $serviceProvider->update(['status' => 'active']);

        return response()->json([
            'message' => 'Service provider activated successfully',
            'data' => $serviceProvider,
        ]);
    }

    public function suspend(ServiceProvider $serviceProvider)
    {
        $serviceProvider->update(['status' => 'suspended']);

        return response()->json([
            'message' => 'Service provider suspended successfully',
            'data' => $serviceProvider,
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total' => ServiceProvider::count(),
            'active' => ServiceProvider::where('status', 'active')->count(),
            'pending' => ServiceProvider::where('status', 'pending')->count(),
            'suspended' => ServiceProvider::where('status', 'suspended')->count(),
            'with_agreement' => ServiceProvider::where('agreement_signed', true)->count(),
            'total_recording_hours' => round(ServiceProvider::join('recording_sessions', 'service_providers.id', '=', 'recording_sessions.service_provider_id')
                ->sum('recording_sessions.recording_minutes') / 60, 2),
        ];

        return response()->json($stats);
    }

    public function recordingSessions(ServiceProvider $serviceProvider, Request $request)
    {
        $query = $serviceProvider->recordingSessions()
            ->with(['subject', 'topic', 'lessonPlan', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('clock_in', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('clock_in', '<=', $request->to_date);
        }

        $sessions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($sessions);
    }

    public function lessonPlans(ServiceProvider $serviceProvider, Request $request)
    {
        $query = $serviceProvider->lessonPlans()
            ->with(['topic.subject', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $plans = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($plans);
    }

    public function downloadAgreement(ServiceProvider $serviceProvider)
    {
        $data = [
            'provider' => $serviceProvider,
            'company' => [
                'name' => 'Studyseco.com',
                'address' => 'Blantyre, Malawi',
                'phone' => '+265 999 000 000',
                'email' => 'info@studyseco.com',
            ],
            'agreement_number' => 'AGR-' . str_pad($serviceProvider->id, 5, '0', STR_PAD_LEFT),
            'effective_date' => $serviceProvider->created_at->format('F d, Y'),
            'signed_date' => $serviceProvider->agreement_signed_at?->format('F d, Y'),
            'generated_at' => now()->format('F d, Y H:i'),
        ];

        $pdf = Pdf::loadView('agreements.service-provider', $data);

        return $pdf->download("agreement-{$serviceProvider->id}-{$serviceProvider->name}.pdf");
    }

    public function signAgreement(Request $request, ServiceProvider $serviceProvider)
    {
        $serviceProvider->update([
            'agreement_signed' => true,
            'agreement_signed_at' => now(),
        ]);

        // Create agreement record
        ServiceProviderAgreement::create([
            'service_provider_id' => $serviceProvider->id,
            'agreement_type' => 'service_contract',
            'signed_at' => now(),
            'terms_content' => 'Standard service provider agreement for educational content creation.',
        ]);

        return response()->json([
            'message' => 'Agreement signed successfully',
            'data' => $serviceProvider->fresh(),
        ]);
    }

    public function payments(ServiceProvider $serviceProvider, Request $request)
    {
        $payments = $serviceProvider->payments()
            ->with('processedBy')
            ->orderBy('payment_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($payments);
    }

    public function paymentSummary(ServiceProvider $serviceProvider)
    {
        return response()->json([
            'total_agreed_amount' => $serviceProvider->total_agreed_amount,
            'total_paid' => $serviceProvider->total_paid,
            'balance_remaining' => $serviceProvider->balance_remaining,
            'payment_progress_percent' => $serviceProvider->payment_progress_percent,
            'payment_preference' => $serviceProvider->payment_preference,
            'monthly_amount' => $serviceProvider->monthly_amount,
            'payment_method' => $serviceProvider->payment_method,
            'payment_details' => $serviceProvider->payment_details,
            'payments_count' => $serviceProvider->payments()->where('status', 'completed')->count(),
        ]);
    }

    public function impersonate(ServiceProvider $serviceProvider)
    {
        // Store the admin user ID in session so we can return later
        $adminUser = Auth::user();

        if (!$adminUser) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in as admin to impersonate',
            ], 403);
        }

        // Store admin info in session for later return
        Session::put('impersonating_service_provider', true);
        Session::put('impersonator_admin_id', $adminUser->id);
        Session::put('impersonator_admin_name', $adminUser->name);

        // Log in as the service provider
        Auth::guard('service_provider')->login($serviceProvider);

        return response()->json([
            'success' => true,
            'message' => 'Now impersonating ' . $serviceProvider->name,
            'redirect_url' => '/service-provider/dashboard',
        ]);
    }

    public function stopImpersonating()
    {
        if (!Session::has('impersonating_service_provider')) {
            return response()->json([
                'success' => false,
                'message' => 'Not currently impersonating anyone',
            ], 400);
        }

        // Log out from service provider
        Auth::guard('service_provider')->logout();

        // Clear impersonation session data
        $adminId = Session::get('impersonator_admin_id');
        Session::forget('impersonating_service_provider');
        Session::forget('impersonator_admin_id');
        Session::forget('impersonator_admin_name');

        return response()->json([
            'success' => true,
            'message' => 'Stopped impersonating',
            'redirect_url' => '/admin/service-providers',
        ]);
    }
}
