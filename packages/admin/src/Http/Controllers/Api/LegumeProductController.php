<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumeProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegumeProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = LegumeProduct::query()->with('inventory');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->boolean('low_stock')) {
            $query->whereHas('inventory', function ($q) {
                $q->whereRaw('quantity <= legume_products.low_stock_threshold');
            });
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->input('per_page', 15));

        return response()->json($products);
    }

    /**
     * Get product statistics
     */
    public function statistics()
    {
        $totalProducts = LegumeProduct::count();
        $activeProducts = LegumeProduct::active()->count();

        $lowStockCount = LegumeProduct::with('inventory')
            ->active()
            ->get()
            ->filter(function ($product) {
                $currentStock = $product->inventory?->quantity ?? 0;
                return $currentStock <= $product->low_stock_threshold && $currentStock > 0;
            })->count();

        $outOfStockCount = LegumeProduct::with('inventory')
            ->active()
            ->get()
            ->filter(function ($product) {
                return ($product->inventory?->quantity ?? 0) <= 0;
            })->count();

        $totalInventoryValue = \App\Models\LegumeInventory::sum(\DB::raw('quantity * average_cost'));

        return response()->json([
            'data' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'low_stock' => $lowStockCount,
                'out_of_stock' => $outOfStockCount,
                'total_inventory_value' => round($totalInventoryValue, 2),
            ]
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'sku' => 'nullable|string|max:191|unique:legume_products,sku',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:20',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = LegumeProduct::generateSku($validated['name']);
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('legume-products', 'public');
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['unit'] = $validated['unit'] ?? 'kg';
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 10;

        $product = LegumeProduct::create($validated);

        // Create initial inventory record
        \App\Models\LegumeInventory::create([
            'legume_product_id' => $product->id,
            'quantity' => 0,
            'reserved_quantity' => 0,
            'average_cost' => $validated['buying_price'],
        ]);

        return response()->json($product->load('inventory'), 201);
    }

    /**
     * Display the specified product
     */
    public function show($id)
    {
        $product = LegumeProduct::with(['inventory', 'stockAlerts' => function ($q) {
            $q->unacknowledged();
        }])->findOrFail($id);

        return response()->json(['data' => $product]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        $product = LegumeProduct::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'sku' => 'nullable|string|max:191|unique:legume_products,sku,' . $id,
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:20',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('legume-products', 'public');
        }

        $product->update($validated);

        return response()->json($product->load('inventory'));
    }

    /**
     * Update product prices
     */
    public function updatePrices(Request $request, $id)
    {
        $product = LegumeProduct::findOrFail($id);

        $validated = $request->validate([
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Prices updated successfully',
            'data' => $product->fresh()
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        $product = LegumeProduct::findOrFail($id);

        // Check if product has inventory
        if ($product->inventory && $product->inventory->quantity > 0) {
            return response()->json([
                'message' => 'Cannot delete product with existing stock. Please adjust stock to zero first.'
            ], 400);
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * Get all products for dropdown
     */
    public function dropdown()
    {
        $products = LegumeProduct::active()
            ->select('id', 'name', 'sku', 'selling_price', 'unit')
            ->with('inventory:legume_product_id,quantity')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $products]);
    }
}
