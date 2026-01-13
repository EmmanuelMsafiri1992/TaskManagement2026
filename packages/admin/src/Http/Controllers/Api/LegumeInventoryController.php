<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumeInventory;
use App\Models\LegumeProduct;
use App\Models\LegumeInventoryMovement;
use App\Services\LegumeInventoryService;
use Illuminate\Http\Request;

class LegumeInventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(LegumeInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of inventory
     */
    public function index(Request $request)
    {
        $query = LegumeInventory::query()->with('product');

        if ($request->filled('search')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->boolean('low_stock')) {
            $query->whereHas('product', function ($q) {
                $q->whereRaw('legume_inventory.quantity <= legume_products.low_stock_threshold');
            });
        }

        if ($request->boolean('out_of_stock')) {
            $query->where('quantity', '<=', 0);
        }

        if ($request->boolean('in_stock')) {
            $query->where('quantity', '>', 0);
        }

        $sortBy = $request->input('sort_by', 'quantity');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'product_name') {
            $query->join('legume_products', 'legume_inventory.legume_product_id', '=', 'legume_products.id')
                  ->select('legume_inventory.*')
                  ->orderBy('legume_products.name', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $inventory = $query->paginate($request->input('per_page', 15));

        return response()->json($inventory);
    }

    /**
     * Get inventory statistics
     */
    public function statistics()
    {
        $stats = $this->inventoryService->getStatistics();

        return response()->json(['data' => $stats]);
    }

    /**
     * Display inventory for a specific product
     */
    public function show($productId)
    {
        $product = LegumeProduct::with('inventory')->findOrFail($productId);

        $inventory = $product->inventory;

        if (!$inventory) {
            $inventory = LegumeInventory::create([
                'legume_product_id' => $productId,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'average_cost' => $product->buying_price,
            ]);
        }

        return response()->json([
            'data' => [
                'product' => $product,
                'inventory' => $inventory,
                'is_low_stock' => $inventory->quantity <= $product->low_stock_threshold,
                'is_out_of_stock' => $inventory->quantity <= 0,
            ]
        ]);
    }

    /**
     * Get movement history for a product
     */
    public function movements(Request $request, $productId)
    {
        $product = LegumeProduct::findOrFail($productId);

        $query = LegumeInventoryMovement::with('user')
            ->where('legume_product_id', $productId);

        if ($request->filled('type')) {
            $query->type($request->type);
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $query->orderBy('created_at', 'desc');

        $movements = $query->paginate($request->input('per_page', 20));

        return response()->json($movements);
    }

    /**
     * Manual stock adjustment
     */
    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'legume_product_id' => 'required|exists:legume_products,id',
            'quantity' => 'required|numeric',
            'reason' => 'required|string|max:500',
            'type' => 'nullable|in:adjustment,damage,return',
        ]);

        $type = $validated['type'] ?? 'adjustment';

        if ($type === 'damage') {
            $inventory = $this->inventoryService->recordDamage(
                $validated['legume_product_id'],
                abs($validated['quantity']),
                $validated['reason'],
                auth()->id()
            );
        } else {
            $inventory = $this->inventoryService->adjustStock(
                $validated['legume_product_id'],
                $validated['quantity'],
                $validated['reason'],
                auth()->id()
            );
        }

        return response()->json([
            'message' => 'Stock adjusted successfully',
            'data' => $inventory->load('product'),
        ]);
    }

    /**
     * Get low stock alerts
     */
    public function lowStockAlerts()
    {
        $lowStockProducts = $this->inventoryService->getLowStockProducts();

        return response()->json([
            'data' => $lowStockProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $product->inventory?->quantity ?? 0,
                    'threshold' => $product->low_stock_threshold,
                    'is_out_of_stock' => ($product->inventory?->quantity ?? 0) <= 0,
                ];
            })->values()
        ]);
    }
}
