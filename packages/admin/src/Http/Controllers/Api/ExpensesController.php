<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpensesController extends Controller
{
    /**
     * Display a listing of expenses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Expense::query()->with(['user', 'approver']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->category($request->category);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('expense_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('expense_date', '<=', $request->end_date);
        }

        // Filter by user (for non-admins, show only their expenses)
        if (!auth()->user()->isSuperAdmin() && !$request->filled('user_id')) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'expense_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $expenses = $query->paginate($request->input('per_page', 15));

        return response()->json($expenses);
    }

    /**
     * Get expense statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $query = Expense::query();

        // Filter by user if not admin
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $stats = [
            'total_expenses' => $query->sum('amount'),
            'pending' => $query->clone()->status('pending')->count(),
            'approved' => $query->clone()->status('approved')->count(),
            'rejected' => $query->clone()->status('rejected')->count(),
            'this_month' => $query->clone()->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount'),
            'last_month' => $query->clone()->whereMonth('expense_date', now()->subMonth()->month)
                ->whereYear('expense_date', now()->subMonth()->year)
                ->sum('amount'),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Get expense categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $categories = Expense::whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return response()->json(['data' => $categories]);
    }

    /**
     * Store a newly created expense
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:191',
            'description' => 'required|string',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $path = $file->store('receipts', 'public');
            $validated['receipt_path'] = $path;
            $validated['receipt_name'] = $file->getClientOriginalName();
        }

        $expense = Expense::create($validated);

        return response()->json($expense->load('user'), 201);
    }

    /**
     * Display the specified expense
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $expense = Expense::with(['user', 'approver'])->findOrFail($id);

        // Check authorization
        if (!auth()->user()->isSuperAdmin() && $expense->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $expense]);
    }

    /**
     * Update the specified expense
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        // Check authorization - only owner or admin can edit
        if (!auth()->user()->isSuperAdmin() && $expense->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only pending expenses can be edited
        if ($expense->status !== 'pending') {
            return response()->json(['message' => 'Only pending expenses can be edited'], 400);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:191',
            'description' => 'required|string',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }

            $file = $request->file('receipt');
            $path = $file->store('receipts', 'public');
            $validated['receipt_path'] = $path;
            $validated['receipt_name'] = $file->getClientOriginalName();
        }

        $expense->update($validated);

        return response()->json($expense->load('user'));
    }

    /**
     * Approve an expense
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json(['message' => 'Only pending expenses can be approved'], 400);
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Expense approved successfully', 'data' => $expense->load(['user', 'approver'])]);
    }

    /**
     * Reject an expense
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject($id)
    {
        $expense = Expense::findOrFail($id);

        if ($expense->status !== 'pending') {
            return response()->json(['message' => 'Only pending expenses can be rejected'], 400);
        }

        $expense->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Expense rejected successfully', 'data' => $expense->load(['user', 'approver'])]);
    }

    /**
     * Remove the specified expense
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);

        // Check authorization
        if (!auth()->user()->isSuperAdmin() && $expense->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete receipt if exists
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
