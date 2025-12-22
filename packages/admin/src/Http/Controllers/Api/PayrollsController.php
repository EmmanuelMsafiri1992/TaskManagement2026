<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\EmployeeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollsController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with(['user', 'createdBy', 'approvedBy']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by period
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->forPeriod($request->start_date, $request->end_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $payrolls = $query->paginate($perPage);

        return response()->json([
            'data' => $payrolls->items(),
            'meta' => [
                'current_page' => $payrolls->currentPage(),
                'last_page' => $payrolls->lastPage(),
                'per_page' => $payrolls->perPage(),
                'total' => $payrolls->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'payroll_period' => 'required|string|max:191',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_type' => 'required|in:allowance,bonus,deduction',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.category' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['currency'] = $validated['currency'] ?? 'MWK';
        $validated['status'] = 'draft';
        $validated['allowances'] = $validated['allowances'] ?? 0;
        $validated['bonuses'] = $validated['bonuses'] ?? 0;
        $validated['deductions'] = $validated['deductions'] ?? 0;

        DB::beginTransaction();
        try {
            $payroll = Payroll::create($validated);

            // Create payroll items if provided
            if (isset($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $payroll->items()->create($item);
                }

                // Recalculate totals based on items
                $payroll->allowances = $payroll->items()->allowances()->sum('amount');
                $payroll->bonuses = $payroll->items()->bonuses()->sum('amount');
                $payroll->deductions = $payroll->items()->deductions()->sum('amount');
            }

            $payroll->calculateTotals();

            DB::commit();

            $payroll->load(['user', 'items']);

            return response()->json([
                'message' => 'Payroll created successfully',
                'data' => $payroll,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create payroll', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $payroll = Payroll::with(['user', 'items', 'createdBy', 'approvedBy'])->findOrFail($id);
        return response()->json(['data' => $payroll]);
    }

    public function update(Request $request, $id)
    {
        $payroll = Payroll::findOrFail($id);

        // Only draft payrolls can be updated
        if ($payroll->status !== 'draft') {
            return response()->json(['message' => 'Only draft payrolls can be updated'], 403);
        }

        $validated = $request->validate([
            'payroll_period' => 'required|string|max:191',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_type' => 'required|in:allowance,bonus,deduction',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.category' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payroll->update($validated);

            // Update items if provided
            if (isset($validated['items'])) {
                $payroll->items()->delete();
                foreach ($validated['items'] as $item) {
                    $payroll->items()->create($item);
                }

                // Recalculate totals based on items
                $payroll->allowances = $payroll->items()->allowances()->sum('amount');
                $payroll->bonuses = $payroll->items()->bonuses()->sum('amount');
                $payroll->deductions = $payroll->items()->deductions()->sum('amount');
            }

            $payroll->calculateTotals();

            DB::commit();

            $payroll->load(['user', 'items']);

            return response()->json([
                'message' => 'Payroll updated successfully',
                'data' => $payroll,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update payroll', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);

        // Only draft payrolls can be deleted
        if ($payroll->status !== 'draft') {
            return response()->json(['message' => 'Only draft payrolls can be deleted'], 403);
        }

        $payroll->items()->delete();
        $payroll->delete();

        return response()->json([
            'message' => 'Payroll deleted successfully',
        ]);
    }

    public function approve($id)
    {
        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'draft') {
            return response()->json(['message' => 'Only draft payrolls can be approved'], 400);
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $payroll->load(['user', 'items', 'approvedBy']);

        return response()->json([
            'message' => 'Payroll approved successfully',
            'data' => $payroll,
        ]);
    }

    public function markAsPaid($id)
    {
        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'approved') {
            return response()->json(['message' => 'Only approved payrolls can be marked as paid'], 400);
        }

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $payroll->load(['user', 'items']);

        return response()->json([
            'message' => 'Payroll marked as paid successfully',
            'data' => $payroll,
        ]);
    }

    public function sendPayslip($id)
    {
        $payroll = Payroll::findOrFail($id);

        if (!in_array($payroll->status, ['approved', 'paid'])) {
            return response()->json(['message' => 'Only approved or paid payrolls can be sent'], 400);
        }

        // TODO: Implement email sending logic here
        $payroll->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $payroll->load(['user', 'items']);

        return response()->json([
            'message' => 'Payslip sent successfully',
            'data' => $payroll,
        ]);
    }

    public function generateForMonth(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $month = $validated['month'];
        $startDate = \Carbon\Carbon::parse($month)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($month)->endOfMonth();

        // Get employees to generate payroll for
        $query = EmployeeRecord::with('user')->active();

        if (isset($validated['user_ids'])) {
            $query->whereIn('user_id', $validated['user_ids']);
        }

        $employees = $query->get();

        $generated = 0;
        $errors = [];

        foreach ($employees as $employee) {
            try {
                // Check if payroll already exists for this period
                $exists = Payroll::forUser($employee->user_id)
                    ->forPeriod($startDate, $endDate)
                    ->exists();

                if ($exists) {
                    $errors[] = "Payroll already exists for {$employee->user->name}";
                    continue;
                }

                Payroll::create([
                    'user_id' => $employee->user_id,
                    'payroll_period' => $startDate->format('F Y'),
                    'period_start' => $startDate,
                    'period_end' => $endDate,
                    'basic_salary' => $employee->current_salary ?? 0,
                    'allowances' => 0,
                    'bonuses' => 0,
                    'deductions' => 0,
                    'gross_salary' => $employee->current_salary ?? 0,
                    'net_salary' => $employee->current_salary ?? 0,
                    'currency' => $employee->salary_currency ?? 'MWK',
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Failed to generate for {$employee->user->name}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'message' => "Generated payroll for {$generated} employee(s)",
            'generated' => $generated,
            'errors' => $errors,
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_payrolls' => Payroll::count(),
            'draft' => Payroll::draft()->count(),
            'approved' => Payroll::approved()->count(),
            'paid' => Payroll::paid()->count(),
            'this_month_total' => Payroll::whereMonth('period_start', now()->month)
                ->whereYear('period_start', now()->year)
                ->sum('net_salary'),
            'by_status' => Payroll::select('status', DB::raw('count(*) as count'), DB::raw('SUM(net_salary) as total'))
                ->groupBy('status')
                ->get(),
        ];

        return response()->json(['data' => $stats]);
    }
}
