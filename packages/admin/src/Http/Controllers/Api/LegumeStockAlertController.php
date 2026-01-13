<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumeStockAlert;
use Illuminate\Http\Request;

class LegumeStockAlertController extends Controller
{
    /**
     * Display a listing of stock alerts
     */
    public function index(Request $request)
    {
        $query = LegumeStockAlert::query()->with(['product', 'acknowledgedByUser']);

        if ($request->filled('alert_type')) {
            $query->alertType($request->alert_type);
        }

        if ($request->boolean('unacknowledged_only', true)) {
            $query->unacknowledged();
        }

        $query->orderBy('created_at', 'desc');

        $alerts = $query->paginate($request->input('per_page', 15));

        return response()->json($alerts);
    }

    /**
     * Get alert statistics
     */
    public function statistics()
    {
        $unacknowledged = LegumeStockAlert::unacknowledged()->count();
        $lowStock = LegumeStockAlert::unacknowledged()->lowStock()->count();
        $outOfStock = LegumeStockAlert::unacknowledged()->outOfStock()->count();

        return response()->json([
            'data' => [
                'unacknowledged' => $unacknowledged,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
            ]
        ]);
    }

    /**
     * Acknowledge an alert
     */
    public function acknowledge($id)
    {
        $alert = LegumeStockAlert::findOrFail($id);

        if ($alert->is_acknowledged) {
            return response()->json([
                'message' => 'Alert is already acknowledged'
            ], 400);
        }

        $alert->acknowledge(auth()->id());

        return response()->json([
            'message' => 'Alert acknowledged successfully',
            'data' => $alert->fresh()->load(['product', 'acknowledgedByUser'])
        ]);
    }

    /**
     * Acknowledge multiple alerts
     */
    public function acknowledgeMultiple(Request $request)
    {
        $validated = $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'exists:legume_stock_alerts,id',
        ]);

        $count = 0;
        foreach ($validated['alert_ids'] as $alertId) {
            $alert = LegumeStockAlert::find($alertId);
            if ($alert && !$alert->is_acknowledged) {
                $alert->acknowledge(auth()->id());
                $count++;
            }
        }

        return response()->json([
            'message' => "{$count} alerts acknowledged successfully"
        ]);
    }

    /**
     * Acknowledge all unacknowledged alerts
     */
    public function acknowledgeAll()
    {
        $count = LegumeStockAlert::unacknowledged()
            ->update([
                'is_acknowledged' => true,
                'acknowledged_by' => auth()->id(),
                'acknowledged_at' => now(),
            ]);

        return response()->json([
            'message' => "{$count} alerts acknowledged successfully"
        ]);
    }
}
