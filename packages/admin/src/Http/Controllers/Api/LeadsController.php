<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadEmailTemplate;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class LeadsController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::query()->with(['assignedUser', 'activities' => fn($q) => $q->latest()->take(3)]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter by service interest
        if ($request->filled('service_interest')) {
            $query->where('service_interest', $request->service_interest);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter needs follow-up
        if ($request->boolean('needs_follow_up')) {
            $query->needsFollowUp();
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $leads = $query->paginate($request->input('per_page', 15));

        return response()->json($leads);
    }

    public function statistics()
    {
        $stats = [
            'total' => Lead::count(),
            'new' => Lead::where('status', 'new')->count(),
            'contacted' => Lead::where('status', 'contacted')->count(),
            'qualified' => Lead::where('status', 'qualified')->count(),
            'proposal_sent' => Lead::where('status', 'proposal_sent')->count(),
            'negotiation' => Lead::where('status', 'negotiation')->count(),
            'won' => Lead::where('status', 'won')->count(),
            'lost' => Lead::where('status', 'lost')->count(),
            'hot' => Lead::where('priority', 'hot')->active()->count(),
            'needs_follow_up' => Lead::needsFollowUp()->count(),
            'conversion_rate' => $this->calculateConversionRate(),
            'by_source' => Lead::select('source', DB::raw('count(*) as count'))
                              ->groupBy('source')
                              ->pluck('count', 'source'),
            'by_service' => Lead::select('service_interest', DB::raw('count(*) as count'))
                               ->groupBy('service_interest')
                               ->pluck('count', 'service_interest'),
            'this_week' => Lead::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Lead::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    public function pipeline()
    {
        $pipeline = [
            'new' => Lead::where('status', 'new')
                        ->orderBy('priority', 'desc')
                        ->orderBy('score', 'desc')
                        ->get(),
            'contacted' => Lead::where('status', 'contacted')
                              ->orderBy('priority', 'desc')
                              ->orderBy('score', 'desc')
                              ->get(),
            'qualified' => Lead::where('status', 'qualified')
                              ->orderBy('priority', 'desc')
                              ->orderBy('score', 'desc')
                              ->get(),
            'proposal_sent' => Lead::where('status', 'proposal_sent')
                                  ->orderBy('priority', 'desc')
                                  ->orderBy('score', 'desc')
                                  ->get(),
            'negotiation' => Lead::where('status', 'negotiation')
                                ->orderBy('priority', 'desc')
                                ->orderBy('score', 'desc')
                                ->get(),
        ];

        return response()->json(['data' => $pipeline]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'nullable|string|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:191',
            'job_title' => 'nullable|string|max:191',
            'website' => 'nullable|string|max:191',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'service_interest' => 'required|in:web_development,mobile_app,software_development,ui_ux_design,ecommerce,maintenance,consulting,other',
            'project_description' => 'nullable|string',
            'budget_range' => 'nullable|in:under_500,500_1000,1000_5000,5000_10000,10000_plus,not_sure',
            'timeline' => 'nullable|in:immediate,1_month,1_3_months,3_6_months,flexible',
            'source' => 'nullable|in:website,referral,social_media,google_ads,linkedin,facebook,twitter,email_campaign,cold_outreach,other',
            'source_detail' => 'nullable|string|max:191',
            'priority' => 'nullable|in:hot,warm,cold',
            'assigned_to' => 'nullable|exists:users,id',
            'internal_notes' => 'nullable|string',
        ]);

        // Set default values
        $validated['status'] = 'new';
        $validated['priority'] = $validated['priority'] ?? 'warm';
        $validated['source'] = $validated['source'] ?? 'website';

        $lead = Lead::create($validated);
        $lead->updateScore();

        // Log activity
        $lead->logActivity('created', 'Lead Created', 'New lead submitted via admin panel');

        // Send notification to admins
        $this->notifyAdmins($lead);

        return response()->json($lead->fresh(['assignedUser']), 201);
    }

    public function show($id)
    {
        $lead = Lead::with([
            'assignedUser',
            'convertedClient',
            'activities.user'
        ])->findOrFail($id);

        return response()->json(['data' => $lead]);
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:191',
            'last_name' => 'nullable|string|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:191',
            'job_title' => 'nullable|string|max:191',
            'website' => 'nullable|string|max:191',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'service_interest' => 'required|in:web_development,mobile_app,software_development,ui_ux_design,ecommerce,maintenance,consulting,other',
            'project_description' => 'nullable|string',
            'budget_range' => 'nullable|in:under_500,500_1000,1000_5000,5000_10000,10000_plus,not_sure',
            'timeline' => 'nullable|in:immediate,1_month,1_3_months,3_6_months,flexible',
            'status' => 'required|in:new,contacted,qualified,proposal_sent,negotiation,won,lost',
            'priority' => 'required|in:hot,warm,cold',
            'source' => 'nullable|in:website,referral,social_media,google_ads,linkedin,facebook,twitter,email_campaign,cold_outreach,other',
            'source_detail' => 'nullable|string|max:191',
            'assigned_to' => 'nullable|exists:users,id',
            'next_follow_up_at' => 'nullable|date',
            'internal_notes' => 'nullable|string',
            'loss_reason' => 'nullable|string|max:191',
        ]);

        // Track status change
        $oldStatus = $lead->status;

        $lead->update($validated);
        $lead->updateScore();

        // Log status change if changed
        if ($oldStatus !== $lead->status) {
            $lead->logActivity('status_changed', 'Status Changed', "Status changed from {$oldStatus} to {$lead->status}");
        }

        return response()->json($lead->fresh(['assignedUser']));
    }

    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully']);
    }

    public function updateStatus(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:new,contacted,qualified,proposal_sent,negotiation,won,lost',
            'loss_reason' => 'required_if:status,lost|nullable|string|max:191',
        ]);

        $oldStatus = $lead->status;
        $lead->update($validated);

        if ($validated['status'] === 'contacted') {
            $lead->last_contacted_at = now();
            $lead->save();
        }

        $lead->logActivity('status_changed', 'Status Changed', "Status changed from {$oldStatus} to {$lead->status}");

        return response()->json($lead);
    }

    public function assignUser(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $lead->update($validated);

        $user = User::find($validated['assigned_to']);
        $lead->logActivity('note_added', 'Lead Assigned', "Lead assigned to {$user->name}");

        return response()->json($lead->fresh(['assignedUser']));
    }

    public function scheduleFollowUp(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'next_follow_up_at' => 'required|date|after:now',
            'note' => 'nullable|string',
        ]);

        $lead->next_follow_up_at = $validated['next_follow_up_at'];
        $lead->save();

        $lead->logActivity(
            'follow_up_scheduled',
            'Follow-up Scheduled',
            $validated['note'] ?? "Follow-up scheduled for {$lead->next_follow_up_at->format('M d, Y H:i')}"
        );

        return response()->json($lead);
    }

    public function addNote(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        $lead->logActivity('note_added', 'Note Added', $validated['note']);

        // Optionally append to internal notes
        if ($request->boolean('add_to_internal_notes')) {
            $lead->internal_notes = ($lead->internal_notes ? $lead->internal_notes . "\n\n" : '')
                                  . "[" . now()->format('Y-m-d H:i') . "] " . $validated['note'];
            $lead->save();
        }

        return response()->json(['message' => 'Note added successfully']);
    }

    public function logCall(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:call_made,call_received',
            'outcome' => 'nullable|string',
            'duration' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $lead->last_contacted_at = now();
        if ($lead->status === 'new') {
            $lead->status = 'contacted';
        }
        $lead->save();

        $lead->logActivity(
            $validated['type'],
            $validated['type'] === 'call_made' ? 'Call Made' : 'Call Received',
            $validated['notes'] ?? $validated['outcome'],
            [
                'duration' => $validated['duration'] ?? null,
                'outcome' => $validated['outcome'] ?? null,
            ]
        );

        return response()->json($lead);
    }

    public function convertToClient($id)
    {
        $lead = Lead::findOrFail($id);

        if ($lead->converted_to_client_id) {
            return response()->json([
                'message' => 'Lead is already converted',
                'client_id' => $lead->converted_to_client_id
            ], 422);
        }

        $client = $lead->convertToClient();

        $lead->logActivity('status_changed', 'Converted to Client', "Lead converted to client #{$client->id}");

        return response()->json([
            'message' => 'Lead converted to client successfully',
            'client' => $client,
            'lead' => $lead->fresh()
        ]);
    }

    public function activities($id)
    {
        $lead = Lead::findOrFail($id);

        $activities = $lead->activities()
                          ->with('user')
                          ->paginate(20);

        return response()->json($activities);
    }

    public function emailTemplates()
    {
        $templates = LeadEmailTemplate::active()->get();
        return response()->json(['data' => $templates]);
    }

    public function storeEmailTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'subject' => 'required|string|max:191',
            'body' => 'required|string',
            'type' => 'required|in:initial_contact,follow_up,proposal,thank_you,custom',
        ]);

        $template = LeadEmailTemplate::create($validated);

        return response()->json($template, 201);
    }

    public function updateEmailTemplate(Request $request, $id)
    {
        $template = LeadEmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'subject' => 'required|string|max:191',
            'body' => 'required|string',
            'type' => 'required|in:initial_contact,follow_up,proposal,thank_you,custom',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json($template);
    }

    public function deleteEmailTemplate($id)
    {
        $template = LeadEmailTemplate::findOrFail($id);
        $template->delete();

        return response()->json(['message' => 'Template deleted successfully']);
    }

    public function options()
    {
        return response()->json([
            'data' => [
                'service_interests' => Lead::SERVICE_INTERESTS,
                'budget_ranges' => Lead::BUDGET_RANGES,
                'timelines' => Lead::TIMELINES,
                'sources' => Lead::SOURCES,
                'statuses' => [
                    'new' => 'New Lead',
                    'contacted' => 'Contacted',
                    'qualified' => 'Qualified',
                    'proposal_sent' => 'Proposal Sent',
                    'negotiation' => 'In Negotiation',
                    'won' => 'Won',
                    'lost' => 'Lost',
                ],
                'priorities' => [
                    'hot' => 'Hot',
                    'warm' => 'Warm',
                    'cold' => 'Cold',
                ],
                'assignable_users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
            ]
        ]);
    }

    public function export(Request $request)
    {
        $query = Lead::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $leads = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        $csvData[] = ['Name', 'Email', 'Phone', 'Company', 'Service Interest', 'Budget', 'Status', 'Priority', 'Source', 'Created At'];

        foreach ($leads as $lead) {
            $csvData[] = [
                $lead->full_name,
                $lead->email,
                $lead->phone,
                $lead->company_name,
                $lead->service_interest_label,
                $lead->budget_range_label,
                $lead->status_label,
                ucfirst($lead->priority),
                Lead::SOURCES[$lead->source] ?? $lead->source,
                $lead->created_at->format('Y-m-d H:i'),
            ];
        }

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        $filename = 'leads_export_' . now()->format('Y-m-d_His') . '.csv';

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function calculateConversionRate(): float
    {
        $total = Lead::count();
        if ($total === 0) return 0;

        $won = Lead::where('status', 'won')->count();
        return round(($won / $total) * 100, 2);
    }

    private function notifyAdmins(Lead $lead)
    {
        // Get admin users to notify
        $admins = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->get();

        if ($admins->isEmpty()) {
            // Fallback to first user if no admins
            $admins = User::take(1)->get();
        }

        // Queue notification (you'll need to create this notification class)
        // Notification::send($admins, new NewLeadNotification($lead));
    }
}
