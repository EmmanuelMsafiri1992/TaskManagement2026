<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumePurchase;
use App\Models\LegumeBudget;
use App\Models\Supplier;
use App\Services\LegumeInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LegumePurchaseController extends Controller
{
    protected $inventoryService;

    public function __construct(LegumeInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of purchases
     */
    public function index(Request $request)
    {
        $query = LegumePurchase::query()->with(['user', 'supplier', 'product']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('legume_product_id')) {
            $query->where('legume_product_id', $request->legume_product_id);
        }

        if ($request->filled('start_date')) {
            $query->where('purchase_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('purchase_date', '<=', $request->end_date);
        }

        if ($request->filled('quality_grade')) {
            $query->where('quality_grade', $request->quality_grade);
        }

        $sortBy = $request->input('sort_by', 'purchase_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $purchases = $query->paginate($request->input('per_page', 15));

        return response()->json($purchases);
    }

    /**
     * Get purchase statistics
     */
    public function statistics()
    {
        $totalPurchases = LegumePurchase::count();
        $completedPurchases = LegumePurchase::completed()->count();
        $pendingPurchases = LegumePurchase::pending()->count();

        $totalSpent = LegumePurchase::completed()->sum('grand_total');
        $totalQuantity = LegumePurchase::completed()->sum('quantity');

        // This month
        $thisMonthSpent = LegumePurchase::completed()
            ->whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('grand_total');

        // Current budget
        $currentBudget = LegumeBudget::getCurrentBudget();

        return response()->json([
            'data' => [
                'total_purchases' => $totalPurchases,
                'completed_purchases' => $completedPurchases,
                'pending_purchases' => $pendingPurchases,
                'total_spent' => round($totalSpent, 2),
                'total_quantity' => round($totalQuantity, 3),
                'this_month_spent' => round($thisMonthSpent, 2),
                'current_budget' => round($currentBudget, 2),
            ]
        ]);
    }

    /**
     * Store a newly created purchase
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'legume_product_id' => 'required|exists:legume_products,id',
            'purchase_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.001',
            'price_per_unit' => 'required|numeric|min:0',
            'packaging_cost' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'other_costs' => 'nullable|numeric|min:0',
            'quality_grade' => 'nullable|in:A,B,C',
            'quality_notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'auto_complete' => 'nullable|boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';
        $validated['currency'] = 'MWK';

        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('purchase-receipts', 'public');
        }

        $purchase = LegumePurchase::create($validated);

        // Auto-complete if requested
        if ($request->boolean('auto_complete')) {
            return $this->complete($purchase->id);
        }

        return response()->json($purchase->load(['user', 'supplier', 'product']), 201);
    }

    /**
     * Display the specified purchase
     */
    public function show($id)
    {
        $purchase = LegumePurchase::with(['user', 'supplier', 'product'])->findOrFail($id);

        return response()->json(['data' => $purchase]);
    }

    /**
     * Update the specified purchase
     */
    public function update(Request $request, $id)
    {
        $purchase = LegumePurchase::findOrFail($id);

        if ($purchase->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending purchases can be edited'
            ], 400);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'legume_product_id' => 'required|exists:legume_products,id',
            'purchase_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.001',
            'price_per_unit' => 'required|numeric|min:0',
            'packaging_cost' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'other_costs' => 'nullable|numeric|min:0',
            'quality_grade' => 'nullable|in:A,B,C',
            'quality_notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('receipt')) {
            if ($purchase->receipt_path) {
                Storage::disk('public')->delete($purchase->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')->store('purchase-receipts', 'public');
        }

        $purchase->update($validated);

        return response()->json($purchase->load(['user', 'supplier', 'product']));
    }

    /**
     * Mark purchase as completed
     */
    public function complete($id)
    {
        $purchase = LegumePurchase::findOrFail($id);

        if ($purchase->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending purchases can be completed'
            ], 400);
        }

        // Check budget
        $currentBudget = LegumeBudget::getCurrentBudget();
        if ($currentBudget < $purchase->grand_total) {
            return response()->json([
                'message' => 'Insufficient budget. Current budget: MWK ' . number_format($currentBudget, 2) . ', Required: MWK ' . number_format($purchase->grand_total, 2)
            ], 400);
        }

        return DB::transaction(function () use ($purchase) {
            // Update purchase status
            $purchase->update(['status' => 'completed']);

            // Add to inventory
            $this->inventoryService->addStock(
                $purchase->legume_product_id,
                $purchase->quantity,
                $purchase->price_per_unit,
                $purchase,
                auth()->id()
            );

            // Deduct from budget
            LegumeBudget::addBudget(
                $purchase->grand_total,
                'deduction',
                "Purchase #{$purchase->purchase_number} - {$purchase->product->name}",
                auth()->id()
            );

            // Update supplier total
            $purchase->supplier->updateTotalSupplied();

            return response()->json([
                'message' => 'Purchase completed successfully',
                'data' => $purchase->fresh()->load(['user', 'supplier', 'product']),
                'current_budget' => LegumeBudget::getCurrentBudget(),
            ]);
        });
    }

    /**
     * Cancel a purchase
     */
    public function cancel($id)
    {
        $purchase = LegumePurchase::findOrFail($id);

        if ($purchase->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending purchases can be cancelled'
            ], 400);
        }

        $purchase->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Purchase cancelled successfully',
            'data' => $purchase->fresh()
        ]);
    }

    /**
     * Get purchases by supplier
     */
    public function bySupplier($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);

        $purchases = $supplier->purchases()
            ->with('product:id,name,sku')
            ->orderBy('purchase_date', 'desc')
            ->paginate(15);

        return response()->json($purchases);
    }

    /**
     * Remove the specified purchase
     */
    public function destroy($id)
    {
        $purchase = LegumePurchase::findOrFail($id);

        if ($purchase->status === 'completed') {
            return response()->json([
                'message' => 'Completed purchases cannot be deleted. They affect inventory records.'
            ], 400);
        }

        if ($purchase->receipt_path) {
            Storage::disk('public')->delete($purchase->receipt_path);
        }

        $purchase->delete();

        return response()->json(['message' => 'Purchase deleted successfully']);
    }
}
