<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Quotation;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    /**
     * Display a listing of income
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Income::query()->with(['user', 'receiver', 'quotation']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->source($request->source);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('income_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('income_date', '<=', $request->end_date);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'income_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $income = $query->paginate($request->input('per_page', 15));

        return response()->json($income);
    }

    /**
     * Get income statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total_income' => Income::sum('amount'),
            'pending' => Income::status('pending')->count(),
            'received' => Income::status('received')->count(),
            'cancelled' => Income::status('cancelled')->count(),
            'this_month' => Income::whereMonth('income_date', now()->month)
                ->whereYear('income_date', now()->year)
                ->sum('amount'),
            'last_month' => Income::whereMonth('income_date', now()->subMonth()->month)
                ->whereYear('income_date', now()->subMonth()->year)
                ->sum('amount'),
            'by_source' => Income::selectRaw('source, SUM(amount) as total')
                ->groupBy('source')
                ->get()
                ->pluck('total', 'source'),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Store a newly created income
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'income_date' => 'required|date',
            'source' => 'required|in:sales,services,consulting,adsense,quotation,other',
            'category' => 'nullable|string|max:191',
            'description' => 'required|string',
            'invoice_number' => 'nullable|string|max:191',
            'client_name' => 'nullable|string|max:191',
            'quotation_id' => 'nullable|exists:quotations,id',
            'status' => 'nullable|in:pending,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'pending';

        $income = Income::create($validated);

        return response()->json($income->load(['user', 'quotation']), 201);
    }

    /**
     * Display the specified income
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $income = Income::with(['user', 'receiver', 'quotation'])->findOrFail($id);

        return response()->json(['data' => $income]);
    }

    /**
     * Update the specified income
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $income = Income::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'income_date' => 'required|date',
            'source' => 'required|in:sales,services,consulting,adsense,quotation,other',
            'category' => 'nullable|string|max:191',
            'description' => 'required|string',
            'invoice_number' => 'nullable|string|max:191',
            'client_name' => 'nullable|string|max:191',
            'quotation_id' => 'nullable|exists:quotations,id',
            'status' => 'nullable|in:pending,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        $income->update($validated);

        return response()->json($income->load(['user', 'quotation']));
    }

    /**
     * Mark income as received
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsReceived($id)
    {
        $income = Income::findOrFail($id);

        if ($income->status === 'received') {
            return response()->json(['message' => 'Income already marked as received'], 400);
        }

        $income->update([
            'status' => 'received',
            'received_by' => auth()->id(),
            'received_at' => now(),
        ]);

        return response()->json(['message' => 'Income marked as received successfully', 'data' => $income->load(['user', 'receiver'])]);
    }

    /**
     * Remove the specified income
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $income = Income::findOrFail($id);
        $income->delete();

        return response()->json(['message' => 'Income deleted successfully']);
    }
}
