<?php

namespace Admin\Http\Controllers\Api;

use App\Models\AuditTrail;
use Illuminate\Http\Request;

class AuditTrailController
{
    /**
     * Get all audit trails with filtering.
     */
    public function index(Request $request)
    {
        $query = AuditTrail::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by model type
        if ($request->has('model_type') && $request->model_type) {
            $query->where('auditable_type', 'like', '%' . $request->model_type . '%');
        }

        // Filter by event type
        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search in old/new values
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('old_values', 'like', '%' . $search . '%')
                  ->orWhere('new_values', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($request->per_page ?? 20);
    }

    /**
     * Get audit trail for a specific model.
     */
    public function show($type, $id)
    {
        $modelClass = 'App\\Models\\' . ucfirst($type);

        return AuditTrail::with('user')
            ->where('auditable_type', $modelClass)
            ->where('auditable_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get available model types for filtering.
     */
    public function modelTypes()
    {
        return AuditTrail::select('auditable_type')
            ->distinct()
            ->pluck('auditable_type')
            ->map(fn($type) => [
                'value' => $type,
                'label' => class_basename($type),
            ]);
    }

    /**
     * Get statistics summary.
     */
    public function statistics()
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'total' => AuditTrail::count(),
            'today' => AuditTrail::where('created_at', '>=', $today)->count(),
            'this_week' => AuditTrail::where('created_at', '>=', $thisWeek)->count(),
            'this_month' => AuditTrail::where('created_at', '>=', $thisMonth)->count(),
            'by_event' => AuditTrail::selectRaw('event, COUNT(*) as count')
                ->groupBy('event')
                ->pluck('count', 'event'),
            'by_model' => AuditTrail::selectRaw('auditable_type, COUNT(*) as count')
                ->groupBy('auditable_type')
                ->get()
                ->map(fn($item) => [
                    'model' => class_basename($item->auditable_type),
                    'count' => $item->count,
                ]),
        ];
    }
}
