<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProfitLossService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    protected $profitLossService;

    public function __construct(ProfitLossService $profitLossService)
    {
        $this->profitLossService = $profitLossService;
    }

    /**
     * Get dashboard overview
     */
    public function dashboard()
    {
        $data = $this->profitLossService->dashboard();

        return response()->json(['data' => $data]);
    }

    /**
     * Get monthly report
     */
    public function monthlyReport(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $report = $this->profitLossService->monthlyReport($year, $month);

        return response()->json(['data' => $report]);
    }

    /**
     * Get yearly report
     */
    public function yearlyReport(Request $request)
    {
        $year = $request->input('year', now()->year);

        $report = $this->profitLossService->yearlyReport($year);

        return response()->json(['data' => $report]);
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

        $report = $this->profitLossService->customPeriodReport($startDate, $endDate);

        return response()->json(['data' => $report]);
    }
}
