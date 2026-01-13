<?php

namespace App\Services;

use App\Models\LegumeOrder;
use App\Models\LegumeOrderItem;
use App\Models\LegumePurchase;
use App\Models\LegumeDeliveryTracking;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LegumeProfitService
{
    /**
     * Calculate profit for a specific period
     *
     * Profit = Revenue - Cost of Goods Sold - Operating Expenses - Delivery Costs
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function calculateProfit(Carbon $startDate, Carbon $endDate): array
    {
        // Revenue from completed/delivered orders that are paid
        $revenue = LegumeOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('order_status', ['delivered', 'ready', 'shipped'])
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // Cost of Goods Sold (using cost_price captured at time of sale)
        $cogs = LegumeOrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('order_date', [$startDate, $endDate])
              ->whereIn('order_status', ['delivered', 'ready', 'shipped']);
        })->sum(DB::raw('quantity * COALESCE(cost_price, 0)'));

        // Purchase-related costs (packaging, transport, etc.)
        $purchaseCosts = LegumePurchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum(DB::raw('COALESCE(packaging_cost, 0) + COALESCE(transport_cost, 0) + COALESCE(other_costs, 0)'));

        // Operating expenses (from existing expense system - filter by legume-related categories)
        $expenses = 0;
        if (class_exists(Expense::class)) {
            $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->where('status', 'approved')
                ->where(function ($q) {
                    $q->where('category', 'like', '%legume%')
                      ->orWhere('category', 'like', '%business%')
                      ->orWhere('category', 'like', '%trading%');
                })
                ->sum('amount');
        }

        // Delivery costs
        $deliveryCosts = LegumeDeliveryTracking::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('order_date', [$startDate, $endDate]);
        })->where('status', 'delivered')
          ->sum('delivery_cost');

        // Calculate profits
        $grossProfit = $revenue - $cogs;
        $totalExpenses = $purchaseCosts + $expenses + $deliveryCosts;
        $netProfit = $grossProfit - $totalExpenses;
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        // Order statistics
        $totalOrders = LegumeOrder::whereBetween('order_date', [$startDate, $endDate])->count();
        $completedOrders = LegumeOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('order_status', ['delivered', 'ready', 'shipped'])
            ->count();
        $paidOrders = LegumeOrder::whereBetween('order_date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->count();

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'label' => $this->getPeriodLabel($startDate, $endDate),
            ],
            'revenue' => round($revenue, 2),
            'cost_of_goods_sold' => round($cogs, 2),
            'gross_profit' => round($grossProfit, 2),
            'gross_margin' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0,
            'expenses' => [
                'purchase_costs' => round($purchaseCosts, 2),
                'operating_expenses' => round($expenses, 2),
                'delivery_costs' => round($deliveryCosts, 2),
                'total' => round($totalExpenses, 2),
            ],
            'net_profit' => round($netProfit, 2),
            'profit_margin' => round($profitMargin, 2),
            'orders' => [
                'total' => $totalOrders,
                'completed' => $completedOrders,
                'paid' => $paidOrders,
            ],
        ];
    }

    /**
     * Calculate profit per product
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function profitByProduct(Carbon $startDate, Carbon $endDate): array
    {
        return LegumeOrderItem::with('product')
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('order_date', [$startDate, $endDate])
                  ->whereIn('order_status', ['delivered', 'ready', 'shipped']);
            })
            ->select(
                'legume_product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(quantity * COALESCE(cost_price, 0)) as total_cost')
            )
            ->groupBy('legume_product_id')
            ->get()
            ->map(function ($item) {
                $profit = $item->total_revenue - $item->total_cost;
                return [
                    'product_id' => $item->legume_product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'sku' => $item->product->sku ?? '',
                    'quantity_sold' => round($item->total_quantity, 3),
                    'revenue' => round($item->total_revenue, 2),
                    'cost' => round($item->total_cost, 2),
                    'profit' => round($profit, 2),
                    'margin' => $item->total_revenue > 0
                        ? round(($profit / $item->total_revenue) * 100, 2)
                        : 0,
                ];
            })
            ->sortByDesc('profit')
            ->values()
            ->toArray();
    }

    /**
     * Get daily report
     *
     * @param Carbon|null $date
     * @return array
     */
    public function dailyReport(?Carbon $date = null): array
    {
        $date = $date ?? now();
        $startDate = $date->copy()->startOfDay();
        $endDate = $date->copy()->endOfDay();

        $report = $this->calculateProfit($startDate, $endDate);
        $report['products'] = $this->profitByProduct($startDate, $endDate);

        return $report;
    }

    /**
     * Get weekly report
     *
     * @param Carbon|null $weekStart
     * @return array
     */
    public function weeklyReport(?Carbon $weekStart = null): array
    {
        $weekStart = $weekStart ?? now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $report = $this->calculateProfit($weekStart, $weekEnd);
        $report['products'] = $this->profitByProduct($weekStart, $weekEnd);

        // Add daily breakdown
        $report['daily_breakdown'] = [];
        for ($i = 0; $i < 7; $i++) {
            $dayStart = $weekStart->copy()->addDays($i)->startOfDay();
            $dayEnd = $dayStart->copy()->endOfDay();

            $dayProfit = $this->calculateProfit($dayStart, $dayEnd);
            $report['daily_breakdown'][] = [
                'date' => $dayStart->toDateString(),
                'day_name' => $dayStart->format('l'),
                'revenue' => $dayProfit['revenue'],
                'profit' => $dayProfit['net_profit'],
                'orders' => $dayProfit['orders']['total'],
            ];
        }

        return $report;
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

        $report = $this->calculateProfit($startDate, $endDate);
        $report['products'] = $this->profitByProduct($startDate, $endDate);

        // Add weekly breakdown
        $report['weekly_breakdown'] = [];
        $currentWeekStart = $startDate->copy()->startOfWeek();

        while ($currentWeekStart->lte($endDate)) {
            $weekEnd = $currentWeekStart->copy()->endOfWeek();

            // Adjust week boundaries to stay within month
            $weekStartAdjusted = $currentWeekStart->lt($startDate) ? $startDate : $currentWeekStart;
            $weekEndAdjusted = $weekEnd->gt($endDate) ? $endDate : $weekEnd;

            $weekProfit = $this->calculateProfit($weekStartAdjusted, $weekEndAdjusted);
            $report['weekly_breakdown'][] = [
                'week_start' => $weekStartAdjusted->toDateString(),
                'week_end' => $weekEndAdjusted->toDateString(),
                'revenue' => $weekProfit['revenue'],
                'profit' => $weekProfit['net_profit'],
                'orders' => $weekProfit['orders']['total'],
            ];

            $currentWeekStart->addWeek();
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
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();

        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $lastMonthStart = $today->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $today->copy()->subMonth()->endOfMonth();

        // Get metrics
        $todayProfit = $this->calculateProfit($todayStart, $todayEnd);
        $weekProfit = $this->calculateProfit($weekStart, $weekEnd);
        $monthProfit = $this->calculateProfit($monthStart, $monthEnd);
        $lastMonthProfit = $this->calculateProfit($lastMonthStart, $lastMonthEnd);

        // Calculate growth
        $revenueGrowth = $lastMonthProfit['revenue'] > 0
            ? round((($monthProfit['revenue'] - $lastMonthProfit['revenue']) / $lastMonthProfit['revenue']) * 100, 2)
            : 0;

        $profitGrowth = $lastMonthProfit['net_profit'] != 0
            ? round((($monthProfit['net_profit'] - $lastMonthProfit['net_profit']) / abs($lastMonthProfit['net_profit'])) * 100, 2)
            : 0;

        // Top products this month
        $topProducts = $this->profitByProduct($monthStart, $monthEnd);
        $topProducts = array_slice($topProducts, 0, 5);

        // Pending orders
        $pendingOrders = LegumeOrder::where('order_status', 'pending')->count();
        $unpaidOrders = LegumeOrder::where('payment_status', 'unpaid')
            ->whereNotIn('order_status', ['cancelled'])
            ->count();

        // Recent orders
        $recentOrders = LegumeOrder::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'Unknown',
                    'total_amount' => $order->total_amount,
                    'order_status' => $order->order_status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at->toDateTimeString(),
                ];
            });

        return [
            'today' => [
                'revenue' => $todayProfit['revenue'],
                'profit' => $todayProfit['net_profit'],
                'orders' => $todayProfit['orders']['total'],
            ],
            'this_week' => [
                'revenue' => $weekProfit['revenue'],
                'profit' => $weekProfit['net_profit'],
                'orders' => $weekProfit['orders']['total'],
            ],
            'this_month' => [
                'revenue' => $monthProfit['revenue'],
                'profit' => $monthProfit['net_profit'],
                'orders' => $monthProfit['orders']['total'],
                'expenses' => $monthProfit['expenses']['total'],
            ],
            'last_month' => [
                'revenue' => $lastMonthProfit['revenue'],
                'profit' => $lastMonthProfit['net_profit'],
                'orders' => $lastMonthProfit['orders']['total'],
            ],
            'growth' => [
                'revenue' => $revenueGrowth,
                'profit' => $profitGrowth,
            ],
            'pending_orders' => $pendingOrders,
            'unpaid_orders' => $unpaidOrders,
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
        ];
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
