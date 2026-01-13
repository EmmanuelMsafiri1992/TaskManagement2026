<?php

namespace App\Services;

use App\Models\Income;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitLossService
{
    /**
     * Calculate profit and loss for a specific period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function calculateProfitLoss(Carbon $startDate, Carbon $endDate): array
    {
        // Get all income for the period
        $incomeData = $this->getIncomeBreakdown($startDate, $endDate);

        // Get all expenses for the period
        $expenseData = $this->getExpenseBreakdown($startDate, $endDate);

        // Calculate totals
        $totalIncome = $incomeData['total'];
        $totalExpenses = $expenseData['total'];
        $netProfitLoss = $totalIncome - $totalExpenses;
        $profitMargin = $totalIncome > 0 ? ($netProfitLoss / $totalIncome) * 100 : 0;

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'label' => $this->getPeriodLabel($startDate, $endDate),
            ],
            'income' => $incomeData,
            'expenses' => $expenseData,
            'summary' => [
                'total_income' => round($totalIncome, 2),
                'total_expenses' => round($totalExpenses, 2),
                'net_profit_loss' => round($netProfitLoss, 2),
                'profit_margin' => round($profitMargin, 2),
                'is_profit' => $netProfitLoss >= 0,
            ],
        ];
    }

    /**
     * Get income breakdown by source/category
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getIncomeBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $incomes = Income::whereBetween('income_date', [$startDate, $endDate])
            ->where('status', 'received')
            ->get();

        // Group by source
        $bySource = $incomes->groupBy('source')->map(function ($items, $source) {
            return [
                'source' => $source,
                'amount' => round($items->sum('amount'), 2),
                'count' => $items->count(),
            ];
        })->values()->toArray();

        // Group by category
        $byCategory = $incomes->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category ?: 'Uncategorized',
                'amount' => round($items->sum('amount'), 2),
                'count' => $items->count(),
            ];
        })->values()->toArray();

        return [
            'total' => round($incomes->sum('amount'), 2),
            'count' => $incomes->count(),
            'by_source' => $bySource,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get expense breakdown by category
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getExpenseBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->get();

        // Group by category
        $byCategory = $expenses->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category ?: 'Uncategorized',
                'amount' => round($items->sum('amount'), 2),
                'count' => $items->count(),
            ];
        })->sortByDesc('amount')->values()->toArray();

        return [
            'total' => round($expenses->sum('amount'), 2),
            'count' => $expenses->count(),
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get monthly report
     *
     * @param int|null $year
     * @param int|null $month
     * @return array
     */
    public function monthlyReport(?int $year = null, ?int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        $report = $this->calculateProfitLoss($startDate, $endDate);

        // Add weekly breakdown
        $report['weekly_breakdown'] = $this->getWeeklyBreakdown($startDate, $endDate);

        // Add comparison with previous month
        $prevMonthStart = $startDate->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $prevMonthStart->copy()->endOfMonth();
        $prevMonthData = $this->calculateProfitLoss($prevMonthStart, $prevMonthEnd);

        $report['comparison'] = [
            'previous_month' => [
                'period' => $prevMonthData['period'],
                'total_income' => $prevMonthData['summary']['total_income'],
                'total_expenses' => $prevMonthData['summary']['total_expenses'],
                'net_profit_loss' => $prevMonthData['summary']['net_profit_loss'],
            ],
            'income_change' => $prevMonthData['summary']['total_income'] > 0
                ? round((($report['summary']['total_income'] - $prevMonthData['summary']['total_income']) / $prevMonthData['summary']['total_income']) * 100, 2)
                : 0,
            'expense_change' => $prevMonthData['summary']['total_expenses'] > 0
                ? round((($report['summary']['total_expenses'] - $prevMonthData['summary']['total_expenses']) / $prevMonthData['summary']['total_expenses']) * 100, 2)
                : 0,
            'profit_change' => abs($prevMonthData['summary']['net_profit_loss']) > 0
                ? round((($report['summary']['net_profit_loss'] - $prevMonthData['summary']['net_profit_loss']) / abs($prevMonthData['summary']['net_profit_loss'])) * 100, 2)
                : 0,
        ];

        return $report;
    }

    /**
     * Get yearly report
     *
     * @param int|null $year
     * @return array
     */
    public function yearlyReport(?int $year = null): array
    {
        $year = $year ?? now()->year;

        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        $report = $this->calculateProfitLoss($startDate, $endDate);

        // Add monthly breakdown
        $report['monthly_breakdown'] = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = Carbon::create($year, $m, 1)->startOfDay();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $monthData = $this->calculateProfitLoss($monthStart, $monthEnd);
            $report['monthly_breakdown'][] = [
                'month' => $m,
                'month_name' => $monthStart->format('F'),
                'income' => $monthData['summary']['total_income'],
                'expenses' => $monthData['summary']['total_expenses'],
                'net_profit_loss' => $monthData['summary']['net_profit_loss'],
            ];
        }

        return $report;
    }

    /**
     * Get custom period report
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function customPeriodReport(Carbon $startDate, Carbon $endDate): array
    {
        $report = $this->calculateProfitLoss($startDate, $endDate);

        // Add daily breakdown if period is less than 35 days
        $daysDiff = $startDate->diffInDays($endDate);
        if ($daysDiff <= 35) {
            $report['daily_breakdown'] = [];
            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $dayStart = $current->copy()->startOfDay();
                $dayEnd = $current->copy()->endOfDay();

                $dayData = $this->calculateProfitLoss($dayStart, $dayEnd);
                $report['daily_breakdown'][] = [
                    'date' => $current->toDateString(),
                    'day_name' => $current->format('l'),
                    'income' => $dayData['summary']['total_income'],
                    'expenses' => $dayData['summary']['total_expenses'],
                    'net_profit_loss' => $dayData['summary']['net_profit_loss'],
                ];

                $current->addDay();
            }
        }

        return $report;
    }

    /**
     * Get dashboard statistics
     *
     * @return array
     */
    public function dashboard(): array
    {
        $today = now();

        // Today
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();
        $todayData = $this->calculateProfitLoss($todayStart, $todayEnd);

        // This Week
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $weekData = $this->calculateProfitLoss($weekStart, $weekEnd);

        // This Month
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $monthData = $this->calculateProfitLoss($monthStart, $monthEnd);

        // Last Month
        $lastMonthStart = $today->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $today->copy()->subMonth()->endOfMonth();
        $lastMonthData = $this->calculateProfitLoss($lastMonthStart, $lastMonthEnd);

        // This Year
        $yearStart = $today->copy()->startOfYear();
        $yearEnd = $today->copy()->endOfYear();
        $yearData = $this->calculateProfitLoss($yearStart, $yearEnd);

        // Calculate growth rates
        $incomeGrowth = $lastMonthData['summary']['total_income'] > 0
            ? round((($monthData['summary']['total_income'] - $lastMonthData['summary']['total_income']) / $lastMonthData['summary']['total_income']) * 100, 2)
            : 0;

        $expenseGrowth = $lastMonthData['summary']['total_expenses'] > 0
            ? round((($monthData['summary']['total_expenses'] - $lastMonthData['summary']['total_expenses']) / $lastMonthData['summary']['total_expenses']) * 100, 2)
            : 0;

        // Recent transactions
        $recentIncome = Income::where('status', 'received')
            ->orderBy('income_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($income) {
                return [
                    'id' => $income->id,
                    'type' => 'income',
                    'amount' => $income->amount,
                    'description' => $income->description,
                    'source' => $income->source,
                    'date' => $income->income_date->toDateString(),
                ];
            });

        $recentExpenses = Expense::where('status', 'approved')
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'type' => 'expense',
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                    'category' => $expense->category,
                    'date' => $expense->expense_date->toDateString(),
                ];
            });

        return [
            'today' => [
                'income' => $todayData['summary']['total_income'],
                'expenses' => $todayData['summary']['total_expenses'],
                'net' => $todayData['summary']['net_profit_loss'],
            ],
            'this_week' => [
                'income' => $weekData['summary']['total_income'],
                'expenses' => $weekData['summary']['total_expenses'],
                'net' => $weekData['summary']['net_profit_loss'],
            ],
            'this_month' => [
                'income' => $monthData['summary']['total_income'],
                'expenses' => $monthData['summary']['total_expenses'],
                'net' => $monthData['summary']['net_profit_loss'],
            ],
            'last_month' => [
                'income' => $lastMonthData['summary']['total_income'],
                'expenses' => $lastMonthData['summary']['total_expenses'],
                'net' => $lastMonthData['summary']['net_profit_loss'],
            ],
            'this_year' => [
                'income' => $yearData['summary']['total_income'],
                'expenses' => $yearData['summary']['total_expenses'],
                'net' => $yearData['summary']['net_profit_loss'],
            ],
            'growth' => [
                'income' => $incomeGrowth,
                'expenses' => $expenseGrowth,
            ],
            'top_expense_categories' => array_slice($monthData['expenses']['by_category'], 0, 5),
            'top_income_sources' => array_slice($monthData['income']['by_source'], 0, 5),
            'recent_income' => $recentIncome,
            'recent_expenses' => $recentExpenses,
        ];
    }

    /**
     * Get weekly breakdown for a period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getWeeklyBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $breakdown = [];
        $currentWeekStart = $startDate->copy()->startOfWeek();

        while ($currentWeekStart->lte($endDate)) {
            $weekEnd = $currentWeekStart->copy()->endOfWeek();

            // Adjust week boundaries to stay within the month
            $weekStartAdjusted = $currentWeekStart->lt($startDate) ? $startDate : $currentWeekStart;
            $weekEndAdjusted = $weekEnd->gt($endDate) ? $endDate : $weekEnd;

            $weekData = $this->calculateProfitLoss($weekStartAdjusted, $weekEndAdjusted);
            $breakdown[] = [
                'week_start' => $weekStartAdjusted->toDateString(),
                'week_end' => $weekEndAdjusted->toDateString(),
                'income' => $weekData['summary']['total_income'],
                'expenses' => $weekData['summary']['total_expenses'],
                'net' => $weekData['summary']['net_profit_loss'],
            ];

            $currentWeekStart->addWeek();
        }

        return $breakdown;
    }

    /**
     * Get period label
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return string
     */
    private function getPeriodLabel(Carbon $startDate, Carbon $endDate): string
    {
        if ($startDate->isSameDay($endDate)) {
            return $startDate->format('M d, Y');
        }

        if ($startDate->isSameMonth($endDate)) {
            return $startDate->format('M d') . ' - ' . $endDate->format('d, Y');
        }

        return $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
    }
}
