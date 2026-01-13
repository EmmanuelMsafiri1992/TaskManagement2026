<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumeBudget;
use Illuminate\Http\Request;

class LegumeBudgetController extends Controller
{
    /**
     * Display a listing of budget entries
     */
    public function index(Request $request)
    {
        $query = LegumeBudget::query()->with('user');

        if ($request->filled('type')) {
            $query->type($request->type);
        }

        if ($request->filled('start_date')) {
            $query->where('budget_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('budget_date', '<=', $request->end_date);
        }

        $query->orderBy('budget_date', 'desc')->orderBy('created_at', 'desc');

        $budgets = $query->paginate($request->input('per_page', 15));

        return response()->json($budgets);
    }

    /**
     * Get current available budget
     */
    public function currentBudget()
    {
        $currentBudget = LegumeBudget::getCurrentBudget();
        $totalAdditions = LegumeBudget::getTotalAdditions();
        $totalDeductions = LegumeBudget::getTotalDeductions();

        return response()->json([
            'data' => [
                'current_budget' => round($currentBudget, 2),
                'total_additions' => round($totalAdditions, 2),
                'total_deductions' => round($totalDeductions, 2),
                'currency' => 'MWK',
            ]
        ]);
    }

    /**
     * Get budget statistics
     */
    public function statistics()
    {
        $currentBudget = LegumeBudget::getCurrentBudget();
        $totalAdditions = LegumeBudget::getTotalAdditions();
        $totalDeductions = LegumeBudget::getTotalDeductions();

        // This month
        $thisMonthAdditions = LegumeBudget::whereIn('type', ['initial', 'addition'])
            ->whereMonth('budget_date', now()->month)
            ->whereYear('budget_date', now()->year)
            ->sum('amount');

        $thisMonthDeductions = abs(LegumeBudget::where('type', 'deduction')
            ->whereMonth('budget_date', now()->month)
            ->whereYear('budget_date', now()->year)
            ->sum('amount'));

        // Recent entries
        $recentEntries = LegumeBudget::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'current_budget' => round($currentBudget, 2),
                'total_additions' => round($totalAdditions, 2),
                'total_deductions' => round($totalDeductions, 2),
                'this_month_additions' => round($thisMonthAdditions, 2),
                'this_month_deductions' => round($thisMonthDeductions, 2),
                'recent_entries' => $recentEntries,
            ]
        ]);
    }

    /**
     * Store a new budget entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:initial,addition,deduction,adjustment',
            'description' => 'nullable|string',
            'budget_date' => 'nullable|date',
        ]);

        // For deductions, make amount negative
        $amount = $validated['amount'];
        if ($validated['type'] === 'deduction') {
            $amount = -abs($amount);
        }

        $budget = LegumeBudget::create([
            'user_id' => auth()->id(),
            'amount' => $amount,
            'currency' => 'MWK',
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'budget_date' => $validated['budget_date'] ?? now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Budget entry created successfully',
            'data' => $budget->load('user'),
            'current_budget' => LegumeBudget::getCurrentBudget(),
        ], 201);
    }
}
