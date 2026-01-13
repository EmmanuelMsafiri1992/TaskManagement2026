<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LegumeProfitService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LegumeProfitReportController extends Controller
{
    protected $profitService;

    public function __construct(LegumeProfitService $profitService)
    {
        $this->profitService = $profitService;
    }

    /**
     * Get dashboard overview
     */
    public function dashboard()
    {
        $data = $this->profitService->dashboard();

        return response()->json(['data' => $data]);
    }

    /**
     * Get daily report
     */
    public function dailyReport(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : now();

        $report = $this->profitService->dailyReport($date);

        return response()->json(['data' => $report]);
    }

    /**
     * Get weekly report
     */
    public function weeklyReport(Request $request)
    {
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek()
            : now()->startOfWeek();

        $report = $this->profitService->weeklyReport($weekStart);

        return response()->json(['data' => $report]);
    }

    /**
     * Get monthly report
     */
    public function monthlyReport(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $report = $this->profitService->monthlyReport($year, $month);

        return response()->json(['data' => $report]);
    }

    /**
     * Get profit by product
     */
    public function profitByProduct(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $products = $this->profitService->profitByProduct($startDate, $endDate);

        return response()->json([
            'data' => [
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'products' => $products,
            ]
        ]);
    }

    /**
     * Get custom period report
     */
    public function customPeriod(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $report = $this->profitService->calculateProfit($startDate, $endDate);
        $report['products'] = $this->profitService->profitByProduct($startDate, $endDate);

        return response()->json(['data' => $report]);
    }
}
