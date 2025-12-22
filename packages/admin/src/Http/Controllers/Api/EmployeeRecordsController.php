<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeRecordsController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeRecord::with(['user', 'supervisor'])
            ->select('employee_records.*');

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by employment status
        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        // Filter by employment type
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }

        // Filter by active/inactive
        if ($request->filled('status_filter')) {
            if ($request->status_filter === 'active') {
                $query->active();
            } elseif ($request->status_filter === 'inactive') {
                $query->inactive();
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'name') {
            $query->join('users', 'employee_records.user_id', '=', 'users.id')
                ->orderBy('users.name', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->get('per_page', 15);
        $employees = $query->paginate($perPage);

        // Get filter options
        $departments = EmployeeRecord::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return response()->json([
            'data' => $employees->items(),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
            'filters' => [
                'departments' => $departments,
                'employment_types' => ['Permanent', 'Contract', 'Probation', 'Casual', 'Internship'],
                'employment_statuses' => ['Active', 'Resigned', 'Terminated', 'Retired', 'Deceased', 'On Leave', 'Suspended'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:employee_records,user_id',
            'national_id' => 'nullable|string|max:191',
            'phone_number' => 'nullable|string|max:191',
            'physical_address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'next_of_kin_name' => 'nullable|string|max:191',
            'next_of_kin_relationship' => 'nullable|string|max:191',
            'next_of_kin_phone' => 'nullable|string|max:191',
            'next_of_kin_address' => 'nullable|string',
            'employment_date' => 'nullable|date',
            'employment_type' => 'required|in:Permanent,Contract,Probation,Casual,Internship',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'probation_period_months' => 'nullable|integer|min:1|max:12',
            'probation_end_date' => 'nullable|date',
            'confirmation_date' => 'nullable|date',
            'position' => 'nullable|string|max:191',
            'department' => 'nullable|string|max:191',
            'reports_to' => 'nullable|exists:users,id',
            'current_salary' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|max:10',
            'employment_status' => 'required|in:Active,Resigned,Terminated,Retired,Deceased,On Leave,Suspended',
            'annual_leave_days' => 'nullable|integer|min:0',
            'sick_leave_days' => 'nullable|integer|min:0',
            'maternity_leave_days' => 'nullable|integer|min:0',
            'tax_identification_number' => 'nullable|string|max:191',
            'pension_number' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['salary_currency'] = $validated['salary_currency'] ?? 'MWK';

        $employee = EmployeeRecord::create($validated);
        $employee->load(['user', 'supervisor']);

        return response()->json([
            'message' => 'Employee record created successfully',
            'data' => $employee,
        ], 201);
    }

    public function show($id)
    {
        $employee = EmployeeRecord::with([
            'user',
            'supervisor',
            'subordinates.user',
            'createdBy',
            'updatedBy'
        ])->findOrFail($id);

        return response()->json(['data' => $employee]);
    }

    public function update(Request $request, $id)
    {
        $employee = EmployeeRecord::findOrFail($id);

        $validated = $request->validate([
            'national_id' => 'nullable|string|max:191',
            'phone_number' => 'nullable|string|max:191',
            'physical_address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
            'next_of_kin_name' => 'nullable|string|max:191',
            'next_of_kin_relationship' => 'nullable|string|max:191',
            'next_of_kin_phone' => 'nullable|string|max:191',
            'next_of_kin_address' => 'nullable|string',
            'employment_date' => 'nullable|date',
            'employment_type' => 'required|in:Permanent,Contract,Probation,Casual,Internship',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'probation_period_months' => 'nullable|integer|min:1|max:12',
            'probation_end_date' => 'nullable|date',
            'confirmation_date' => 'nullable|date',
            'position' => 'nullable|string|max:191',
            'department' => 'nullable|string|max:191',
            'reports_to' => 'nullable|exists:users,id',
            'current_salary' => 'nullable|numeric|min:0',
            'salary_currency' => 'nullable|string|max:10',
            'employment_status' => 'required|in:Active,Resigned,Terminated,Retired,Deceased,On Leave,Suspended',
            'termination_date' => 'nullable|date',
            'termination_type' => 'nullable|in:Resignation,Dismissal,Mutual Agreement,Contract Expiry,Redundancy,Retirement,Death',
            'termination_reason' => 'nullable|string',
            'notice_period_days' => 'nullable|integer|min:0',
            'notice_given_date' => 'nullable|date',
            'last_working_day' => 'nullable|date',
            'annual_leave_days' => 'nullable|integer|min:0',
            'sick_leave_days' => 'nullable|integer|min:0',
            'maternity_leave_days' => 'nullable|integer|min:0',
            'leave_balance_annual' => 'nullable|numeric',
            'leave_balance_sick' => 'nullable|numeric',
            'tax_identification_number' => 'nullable|string|max:191',
            'pension_number' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $employee->update($validated);
        $employee->load(['user', 'supervisor']);

        return response()->json([
            'message' => 'Employee record updated successfully',
            'data' => $employee,
        ]);
    }

    public function destroy($id)
    {
        $employee = EmployeeRecord::findOrFail($id);
        $employee->delete();

        return response()->json([
            'message' => 'Employee record deleted successfully',
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_employees' => EmployeeRecord::count(),
            'active_employees' => EmployeeRecord::active()->count(),
            'on_probation' => EmployeeRecord::onProbation()->count(),
            'by_department' => EmployeeRecord::active()
                ->select('department', DB::raw('count(*) as count'))
                ->whereNotNull('department')
                ->groupBy('department')
                ->get(),
            'by_employment_type' => EmployeeRecord::active()
                ->select('employment_type', DB::raw('count(*) as count'))
                ->groupBy('employment_type')
                ->get(),
            'contracts_expiring_soon' => EmployeeRecord::active()
                ->where('employment_type', 'Contract')
                ->whereNotNull('contract_end_date')
                ->whereBetween('contract_end_date', [now(), now()->addDays(30)])
                ->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    public function availableUsers()
    {
        // Get users who don't have employee records yet
        $usersWithRecords = EmployeeRecord::pluck('user_id');
        $availableUsers = User::whereNotIn('id', $usersWithRecords)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $availableUsers]);
    }

    public function supervisors()
    {
        // Get all users who can be supervisors (typically active employees)
        $supervisors = User::whereHas('employeeRecord', function ($query) {
            $query->active();
        })->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $supervisors]);
    }
}
