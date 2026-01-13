<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request)
    {
        $query = Supplier::query()->withCount('purchases');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('district')) {
            $query->district($request->district);
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $suppliers = $query->paginate($request->input('per_page', 15));

        return response()->json($suppliers);
    }

    /**
     * Get supplier statistics
     */
    public function statistics()
    {
        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::active()->count();
        $totalSupplied = Supplier::sum('total_supplied');

        // Top suppliers by total supplied
        $topSuppliers = Supplier::orderBy('total_supplied', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'total_supplied', 'district']);

        // Districts
        $districts = Supplier::whereNotNull('district')
            ->distinct()
            ->pluck('district');

        return response()->json([
            'data' => [
                'total_suppliers' => $totalSuppliers,
                'active_suppliers' => $activeSuppliers,
                'total_supplied' => round($totalSupplied, 2),
                'top_suppliers' => $topSuppliers,
                'districts' => $districts,
            ]
        ]);
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'nullable|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';
        $validated['total_supplied'] = 0;

        $supplier = Supplier::create($validated);

        return response()->json($supplier, 201);
    }

    /**
     * Display the specified supplier
     */
    public function show($id)
    {
        $supplier = Supplier::withCount('purchases')->findOrFail($id);

        return response()->json(['data' => $supplier]);
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'nullable|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'national_id' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'district' => 'nullable|string|max:100',
            'village' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $supplier->update($validated);

        return response()->json($supplier);
    }

    /**
     * Get purchase history for a supplier
     */
    public function purchaseHistory($id)
    {
        $supplier = Supplier::findOrFail($id);

        $purchases = $supplier->purchases()
            ->with('product:id,name,sku')
            ->orderBy('purchase_date', 'desc')
            ->paginate(15);

        return response()->json($purchases);
    }

    /**
     * Remove the specified supplier
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Check if supplier has purchases
        if ($supplier->purchases()->exists()) {
            return response()->json([
                'message' => 'Cannot delete supplier with existing purchases. Consider marking as inactive instead.'
            ], 400);
        }

        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }

    /**
     * Get all suppliers for dropdown
     */
    public function dropdown()
    {
        $suppliers = Supplier::active()
            ->select('id', 'name', 'phone', 'district')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $suppliers]);
    }
}
