<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdvanceRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvanceRequestsController extends Controller
{
    public function index(Request $request)
    {
        $query = AdvanceRequest::with(['user', 'approvedBy']);

        // For admins, show all requests. For users, show only their own
        if (!auth()->user()->can('advance_request:view-all')) {
            $query->forUser(auth()->id());
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user (for admins)
        if ($request->filled('user_id') && auth()->user()->can('advance_request:view-all')) {
            $query->forUser($request->user_id);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $requests = $query->paginate($perPage);

        return response()->json([
            'data' => $requests->items(),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|max:10',
            'reason' => 'required|string',
            'expected_deduction_date' => 'nullable|date',
        ]);

        // If user_id not provided, use authenticated user
        $validated['user_id'] = $validated['user_id'] ?? auth()->id();
        $validated['status'] = 'pending';
        $validated['currency'] = $validated['currency'] ?? 'MWK';
        $validated['remaining_balance'] = $validated['amount'];

        $advanceRequest = AdvanceRequest::create($validated);
        $advanceRequest->load(['user', 'approvedBy']);

        return response()->json([
            'message' => 'Advance request submitted successfully',
            'data' => $advanceRequest,
        ], 201);
    }

    public function show($id)
    {
        $advanceRequest = AdvanceRequest::with(['user', 'approvedBy', 'payroll'])->findOrFail($id);

        // Check permissions
        if (!auth()->user()->can('advance_request:view-all') && $advanceRequest->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $advanceRequest]);
    }

    public function update(Request $request, $id)
    {
        $advanceRequest = AdvanceRequest::findOrFail($id);

        // Check permissions - only pending requests can be updated by the requester
        if ($advanceRequest->status !== 'pending' || (!auth()->user()->can('advance_request:update-all') && $advanceRequest->user_id !== auth()->id())) {
            return response()->json(['message' => 'Cannot update this advance request'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string',
            'expected_deduction_date' => 'nullable|date',
        ]);

        $validated['remaining_balance'] = $validated['amount'];

        $advanceRequest->update($validated);
        $advanceRequest->load(['user', 'approvedBy']);

        return response()->json([
            'message' => 'Advance request updated successfully',
            'data' => $advanceRequest,
        ]);
    }

    public function destroy($id)
    {
        $advanceRequest = AdvanceRequest::findOrFail($id);

        // Check permissions - only pending requests can be deleted
        if ($advanceRequest->status !== 'pending' || (!auth()->user()->can('advance_request:delete') && $advanceRequest->user_id !== auth()->id())) {
            return response()->json(['message' => 'Cannot delete this advance request'], 403);
        }

        $advanceRequest->delete();

        return response()->json([
            'message' => 'Advance request deleted successfully',
        ]);
    }

    public function approve(Request $request, $id)
    {
        $advanceRequest = AdvanceRequest::findOrFail($id);

        if ($advanceRequest->status !== 'pending') {
            return response()->json(['message' => 'Advance request is not pending'], 400);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
            'expected_deduction_date' => 'nullable|date',
        ]);

        $advanceRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'expected_deduction_date' => $validated['expected_deduction_date'] ?? $advanceRequest->expected_deduction_date,
        ]);

        $advanceRequest->load(['user', 'approvedBy']);

        return response()->json([
            'message' => 'Advance request approved successfully',
            'data' => $advanceRequest,
        ]);
    }

    public function reject(Request $request, $id)
    {
        $advanceRequest = AdvanceRequest::findOrFail($id);

        if ($advanceRequest->status !== 'pending') {
            return response()->json(['message' => 'Advance request is not pending'], 400);
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $advanceRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        $advanceRequest->load(['user', 'approvedBy']);

        return response()->json([
            'message' => 'Advance request rejected',
            'data' => $advanceRequest,
        ]);
    }

    public function deduct(Request $request, $id)
    {
        $advanceRequest = AdvanceRequest::findOrFail($id);

        if ($advanceRequest->status !== 'approved') {
            return response()->json(['message' => 'Can only deduct from approved advances'], 400);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payroll_id' => 'nullable|exists:payrolls,id',
        ]);

        $maxDeductible = $advanceRequest->amount - $advanceRequest->amount_deducted;
        if ($validated['amount'] > $maxDeductible) {
            return response()->json(['message' => "Maximum deductible amount is {$maxDeductible}"], 400);
        }

        $advanceRequest->recordDeduction($validated['amount'], $validated['payroll_id'] ?? null);

        // If payroll_id provided, add as payroll item
        if (isset($validated['payroll_id'])) {
            PayrollItem::create([
                'payroll_id' => $validated['payroll_id'],
                'item_type' => 'deduction',
                'description' => 'Salary Advance Repayment',
                'amount' => $validated['amount'],
                'category' => 'Advance Deduction',
            ]);

            // Update payroll totals
            $payroll = Payroll::find($validated['payroll_id']);
            if ($payroll) {
                $payroll->deductions += $validated['amount'];
                $payroll->net_salary = $payroll->gross_salary - $payroll->deductions;
                $payroll->save();
            }
        }

        $advanceRequest->load(['user', 'approvedBy', 'payroll']);

        return response()->json([
            'message' => 'Deduction recorded successfully',
            'data' => $advanceRequest,
        ]);
    }

    public function statistics()
    {
        $stats = [
            'pending' => AdvanceRequest::pending()->count(),
            'approved' => AdvanceRequest::approved()->count(),
            'rejected' => AdvanceRequest::rejected()->count(),
            'deducted' => AdvanceRequest::deducted()->count(),
            'total_pending_amount' => AdvanceRequest::pending()->sum('amount'),
            'total_approved_amount' => AdvanceRequest::approved()->sum('amount'),
            'total_outstanding' => AdvanceRequest::approved()->sum(DB::raw('amount - amount_deducted')),
            'this_month' => AdvanceRequest::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        return response()->json(['data' => $stats]);
    }

    public function userAdvances($userId)
    {
        // Get all approved but not fully deducted advances for a user
        $advances = AdvanceRequest::forUser($userId)
            ->needsDeduction()
            ->orderBy('created_at', 'asc')
            ->get();

        $totalOutstanding = $advances->sum(function ($advance) {
            return $advance->amount - $advance->amount_deducted;
        });

        return response()->json([
            'data' => $advances,
            'total_outstanding' => $totalOutstanding,
        ]);
    }
}
