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
        // Query users with their employee records (if any)
        // By default, show only active (non-archived) users
        $query = User::with(['employeeRecord.supervisor', 'roles'])
            ->select('users.*');

        // Filter by archived status
        if ($request->filled('archived') && $request->archived === 'true') {
            $query->onlyTrashed();
        } elseif ($request->filled('show_all') && $request->show_all === 'true') {
            $query->withTrashed();
        }
        // Default: only non-archived users (no additional filter needed)

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by employment status (only applies to users with employee records)
        if ($request->filled('employment_status')) {
            $status = $request->employment_status;
            $query->whereHas('employeeRecord', function ($q) use ($status) {
                $q->where('employment_status', $status);
            });
        }

        // Filter by employment type
        if ($request->filled('employment_type')) {
            $type = $request->employment_type;
            $query->whereHas('employeeRecord', function ($q) use ($type) {
                $q->where('employment_type', $type);
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $dept = $request->department;
            $query->whereHas('employeeRecord', function ($q) use ($dept) {
                $q->where('department', $dept);
            });
        }

        // Filter by has employee record or not
        if ($request->filled('has_employee_record')) {
            if ($request->has_employee_record === 'yes') {
                $query->whereHas('employeeRecord');
            } else {
                $query->whereDoesntHave('employeeRecord');
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        // Transform data to include employee details
        $transformedData = $users->getCollection()->map(function ($user) {
            $employee = $user->employeeRecord;
            return [
                'id' => $user->id,
                'user_id' => $user->id,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ],
                'roles' => $user->roles->pluck('name'),
                'has_employee_record' => $employee !== null,
                'employee_record_id' => $employee?->id,
                'position' => $employee?->position,
                'department' => $employee?->department,
                'employment_type' => $employee?->employment_type ?? 'Not Set',
                'employment_status' => $employee?->employment_status ?? 'Not Set',
                'employment_date' => $employee?->employment_date,
                'current_salary' => $employee?->current_salary,
                'salary_currency' => $employee?->salary_currency ?? 'MWK',
                'phone_number' => $employee?->phone_number,
                'supervisor' => $employee?->supervisor ? [
                    'id' => $employee->supervisor->id,
                    'name' => $employee->supervisor->name,
                ] : null,
                // Include all employee record fields for the detail view
                'national_id' => $employee?->national_id,
                'physical_address' => $employee?->physical_address,
                'date_of_birth' => $employee?->date_of_birth,
                'gender' => $employee?->gender,
                'marital_status' => $employee?->marital_status,
                'next_of_kin_name' => $employee?->next_of_kin_name,
                'next_of_kin_relationship' => $employee?->next_of_kin_relationship,
                'next_of_kin_phone' => $employee?->next_of_kin_phone,
                'next_of_kin_address' => $employee?->next_of_kin_address,
                'contract_start_date' => $employee?->contract_start_date,
                'contract_end_date' => $employee?->contract_end_date,
                'probation_end_date' => $employee?->probation_end_date,
                'leave_balance_annual' => $employee?->leave_balance_annual,
                'leave_balance_sick' => $employee?->leave_balance_sick,
                'annual_leave_days' => $employee?->annual_leave_days,
                'sick_leave_days' => $employee?->sick_leave_days,
                'tax_identification_number' => $employee?->tax_identification_number,
                'pension_number' => $employee?->pension_number,
                'notes' => $employee?->notes,
                'is_archived' => $user->trashed(),
                'deleted_at' => $user->deleted_at,
            ];
        });

        // Get filter options
        $departments = EmployeeRecord::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return response()->json([
            'data' => $transformedData,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
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
        $totalUsers = User::count();
        $usersWithRecords = EmployeeRecord::count();

        $stats = [
            'total_employees' => $totalUsers,
            'with_records' => $usersWithRecords,
            'without_records' => $totalUsers - $usersWithRecords,
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
