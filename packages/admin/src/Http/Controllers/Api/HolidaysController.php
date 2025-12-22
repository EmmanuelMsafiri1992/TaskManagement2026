<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidaysController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::with(['createdBy']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->inYear($request->year);
        }

        // Filter by month
        if ($request->filled('month') && $request->filled('year')) {
            $query->inMonth($request->year, $request->month);
        }

        // Filter upcoming or past
        if ($request->filled('filter')) {
            if ($request->filter === 'upcoming') {
                $query->upcoming();
            } elseif ($request->filter === 'past') {
                $query->past();
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 50);
        $holidays = $query->paginate($perPage);

        return response()->json([
            'data' => $holidays->items(),
            'meta' => [
                'current_page' => $holidays->currentPage(),
                'last_page' => $holidays->lastPage(),
                'per_page' => $holidays->perPage(),
                'total' => $holidays->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_recurring'] = $validated['is_recurring'] ?? false;

        $holiday = Holiday::create($validated);
        $holiday->load('createdBy');

        return response()->json([
            'message' => 'Holiday created successfully',
            'data' => $holiday,
        ], 201);
    }

    public function show($id)
    {
        $holiday = Holiday::with('createdBy')->findOrFail($id);
        return response()->json(['data' => $holiday]);
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
        ]);

        $holiday->update($validated);
        $holiday->load('createdBy');

        return response()->json([
            'message' => 'Holiday updated successfully',
            'data' => $holiday,
        ]);
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return response()->json([
            'message' => 'Holiday deleted successfully',
        ]);
    }

    public function upcoming()
    {
        $holidays = Holiday::upcoming()->limit(10)->get();

        return response()->json(['data' => $holidays]);
    }

    public function calendar(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $holidays = Holiday::inMonth($year, $month)->get();

        return response()->json(['data' => $holidays]);
    }
}
