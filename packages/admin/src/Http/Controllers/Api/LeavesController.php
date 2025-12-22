<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\EmployeeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeavesController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::with(['user', 'approvedBy']);

        // For admins, show all leaves. For users, show only their own
        if (!auth()->user()->can('leave:view-all')) {
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

        // Filter by leave type
        if ($request->filled('leave_type')) {
            $query->byType($request->leave_type);
        }

        // Filter by user (for admins)
        if ($request->filled('user_id') && auth()->user()->can('leave:view-all')) {
            $query->forUser($request->user_id);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->inPeriod($request->start_date, $request->end_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $leaves = $query->paginate($perPage);

        return response()->json([
            'data' => $leaves->items(),
            'meta' => [
                'current_page' => $leaves->currentPage(),
                'last_page' => $leaves->lastPage(),
                'per_page' => $leaves->perPage(),
                'total' => $leaves->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'leave_type' => 'required|string|max:191',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days' => 'required|numeric|min:0.5',
            'is_half_day' => 'boolean',
            'half_day_period' => 'nullable|in:morning,afternoon',
            'reason' => 'required|string',
        ]);

        // If user_id not provided, use authenticated user
        $validated['user_id'] = $validated['user_id'] ?? auth()->id();
        $validated['status'] = 'pending';
        $validated['is_half_day'] = $validated['is_half_day'] ?? false;

        $leave = Leave::create($validated);
        $leave->load(['user', 'approvedBy']);

        return response()->json([
            'message' => 'Leave request submitted successfully',
            'data' => $leave,
        ], 201);
    }

    public function show($id)
    {
        $leave = Leave::with(['user', 'approvedBy'])->findOrFail($id);

        // Check permissions
        if (!auth()->user()->can('leave:view-all') && $leave->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $leave]);
    }

    public function update(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        // Check permissions - only pending leaves can be updated by the requester
        if ($leave->status !== 'pending' || (!auth()->user()->can('leave:update-all') && $leave->user_id !== auth()->id())) {
            return response()->json(['message' => 'Cannot update this leave request'], 403);
        }

        $validated = $request->validate([
            'leave_type' => 'required|string|max:191',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days' => 'required|numeric|min:0.5',
            'is_half_day' => 'boolean',
            'half_day_period' => 'nullable|in:morning,afternoon',
            'reason' => 'required|string',
        ]);

        $leave->update($validated);
        $leave->load(['user', 'approvedBy']);

        return response()->json([
            'message' => 'Leave request updated successfully',
            'data' => $leave,
        ]);
    }

    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);

        // Check permissions - only pending leaves can be deleted
        if ($leave->status !== 'pending' || (!auth()->user()->can('leave:delete') && $leave->user_id !== auth()->id())) {
            return response()->json(['message' => 'Cannot delete this leave request'], 403);
        }

        $leave->delete();

        return response()->json([
            'message' => 'Leave request deleted successfully',
        ]);
    }

    public function approve(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'Leave request is not pending'], 400);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        // Update employee leave balance
        $this->updateLeaveBalance($leave, 'deduct');

        $leave->load(['user', 'approvedBy']);

        return response()->json([
            'message' => 'Leave request approved successfully',
            'data' => $leave,
        ]);
    }

    public function reject(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json(['message' => 'Leave request is not pending'], 400);
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        $leave->load(['user', 'approvedBy']);

        return response()->json([
            'message' => 'Leave request rejected',
            'data' => $leave,
        ]);
    }

    public function statistics()
    {
        $stats = [
            'pending' => Leave::pending()->count(),
            'approved' => Leave::approved()->count(),
            'rejected' => Leave::rejected()->count(),
            'this_month' => Leave::approved()
                ->whereYear('start_date', now()->year)
                ->whereMonth('start_date', now()->month)
                ->sum('days'),
            'by_type' => Leave::approved()
                ->select('leave_type', DB::raw('SUM(days) as total_days'), DB::raw('COUNT(*) as count'))
                ->groupBy('leave_type')
                ->get(),
        ];

        return response()->json(['data' => $stats]);
    }

    protected function updateLeaveBalance($leave, $action = 'deduct')
    {
        $employee = EmployeeRecord::where('user_id', $leave->user_id)->first();

        if (!$employee) {
            return;
        }

        $multiplier = $action === 'deduct' ? -1 : 1;

        switch ($leave->leave_type) {
            case 'Annual Leave':
                $employee->leave_balance_annual += ($leave->days * $multiplier);
                break;
            case 'Sick Leave':
                $employee->leave_balance_sick += ($leave->days * $multiplier);
                break;
        }

        $employee->save();
    }
}
