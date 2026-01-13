<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumeOrder;
use App\Models\LegumeOrderItem;
use App\Models\LegumeProduct;
use App\Models\LegumeInventory;
use App\Services\LegumeInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegumeOrderController extends Controller
{
    protected $inventoryService;

    public function __construct(LegumeInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = LegumeOrder::query()->with(['customer', 'user']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('order_status')) {
            $query->orderStatus($request->order_status);
        }

        if ($request->filled('payment_status')) {
            $query->paymentStatus($request->payment_status);
        }

        if ($request->filled('fulfillment_type')) {
            $query->fulfillmentType($request->fulfillment_type);
        }

        if ($request->filled('customer_id')) {
            $query->where('legume_customer_id', $request->customer_id);
        }

        if ($request->filled('start_date')) {
            $query->where('order_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('order_date', '<=', $request->end_date);
        }

        $sortBy = $request->input('sort_by', 'order_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $orders = $query->paginate($request->input('per_page', 15));

        return response()->json($orders);
    }

    /**
     * Get order statistics
     */
    public function statistics()
    {
        $totalOrders = LegumeOrder::count();
        $pendingOrders = LegumeOrder::orderStatus('pending')->count();
        $processingOrders = LegumeOrder::whereIn('order_status', ['confirmed', 'processing', 'ready'])->count();
        $deliveredOrders = LegumeOrder::orderStatus('delivered')->count();

        $unpaidOrders = LegumeOrder::paymentStatus('unpaid')
            ->whereNotIn('order_status', ['cancelled'])
            ->count();

        $totalRevenue = LegumeOrder::where('payment_status', 'paid')->sum('total_amount');
        $pendingPayments = LegumeOrder::whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotIn('order_status', ['cancelled'])
            ->sum(DB::raw('total_amount - amount_paid'));

        // This month
        $thisMonthRevenue = LegumeOrder::where('payment_status', 'paid')
            ->whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year)
            ->sum('total_amount');

        $thisMonthOrders = LegumeOrder::whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year)
            ->count();

        return response()->json([
            'data' => [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'delivered_orders' => $deliveredOrders,
                'unpaid_orders' => $unpaidOrders,
                'total_revenue' => round($totalRevenue, 2),
                'pending_payments' => round($pendingPayments, 2),
                'this_month_revenue' => round($thisMonthRevenue, 2),
                'this_month_orders' => $thisMonthOrders,
            ]
        ]);
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'legume_customer_id' => 'required|exists:legume_customers,id',
            'order_date' => 'required|date',
            'fulfillment_type' => 'required|in:pickup,delivery',
            'discount_amount' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.legume_product_id' => 'required|exists:legume_products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Validate stock availability
            foreach ($validated['items'] as $item) {
                $inventory = LegumeInventory::where('legume_product_id', $item['legume_product_id'])->first();
                $available = $inventory ? $inventory->quantity - $inventory->reserved_quantity : 0;

                if ($available < $item['quantity']) {
                    $product = LegumeProduct::find($item['legume_product_id']);
                    return response()->json([
                        'message' => "Insufficient stock for {$product->name}. Available: {$available} kg"
                    ], 400);
                }
            }

            // Create order
            $order = LegumeOrder::create([
                'legume_customer_id' => $validated['legume_customer_id'],
                'user_id' => auth()->id(),
                'order_date' => $validated['order_date'],
                'fulfillment_type' => $validated['fulfillment_type'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'delivery_fee' => $validated['delivery_fee'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'currency' => 'MWK',
            ]);

            // Add items
            foreach ($validated['items'] as $item) {
                $inventory = LegumeInventory::where('legume_product_id', $item['legume_product_id'])->first();

                LegumeOrderItem::create([
                    'legume_order_id' => $order->id,
                    'legume_product_id' => $item['legume_product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'cost_price' => $inventory?->average_cost ?? 0,
                ]);

                // Reserve stock
                $this->inventoryService->reserveStock($item['legume_product_id'], $item['quantity']);
            }

            // Calculate totals
            $order->calculateTotals();

            return response()->json($order->load(['customer', 'items.product']), 201);
        });
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = LegumeOrder::with(['customer', 'user', 'items.product', 'payments', 'delivery'])
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = LegumeOrder::findOrFail($id);

        $validated = $request->validate([
            'order_status' => 'required|in:pending,confirmed,processing,ready,shipped,delivered,cancelled',
            'cancellation_reason' => 'nullable|required_if:order_status,cancelled|string',
        ]);

        $oldStatus = $order->order_status;
        $newStatus = $validated['order_status'];

        return DB::transaction(function () use ($order, $oldStatus, $newStatus, $validated) {
            // Handle status transitions
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                // Release reserved stock
                foreach ($order->items as $item) {
                    $this->inventoryService->releaseReservedStock(
                        $item->legume_product_id,
                        $item->quantity
                    );
                }
                $order->cancellation_reason = $validated['cancellation_reason'] ?? null;
            }

            if ($newStatus === 'confirmed' && !$order->confirmed_at) {
                $order->confirmed_at = now();

                // Deduct from inventory (move from reserved to actual deduction)
                foreach ($order->items as $item) {
                    // Release reservation first
                    $this->inventoryService->releaseReservedStock(
                        $item->legume_product_id,
                        $item->quantity
                    );

                    // Then deduct actual stock
                    $costPrice = $this->inventoryService->deductStock(
                        $item->legume_product_id,
                        $item->quantity,
                        $item,
                        auth()->id()
                    );

                    // Update cost price on item
                    $item->update(['cost_price' => $costPrice]);
                }
            }

            if ($newStatus === 'shipped' && !$order->shipped_at) {
                $order->shipped_at = now();
            }

            if ($newStatus === 'delivered' && !$order->delivered_at) {
                $order->delivered_at = now();

                // Update customer total purchases if paid
                if ($order->payment_status === 'paid') {
                    $order->customer->updateTotalPurchases();
                }
            }

            $order->order_status = $newStatus;
            $order->save();

            return response()->json([
                'message' => 'Order status updated successfully',
                'data' => $order->fresh()->load(['customer', 'items.product', 'payments', 'delivery'])
            ]);
        });
    }

    /**
     * Add item to order
     */
    public function addItem(Request $request, $id)
    {
        $order = LegumeOrder::findOrFail($id);

        if (!in_array($order->order_status, ['pending'])) {
            return response()->json([
                'message' => 'Items can only be added to pending orders'
            ], 400);
        }

        $validated = $request->validate([
            'legume_product_id' => 'required|exists:legume_products,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_price' => 'required|numeric|min:0',
        ]);

        // Check stock
        $inventory = LegumeInventory::where('legume_product_id', $validated['legume_product_id'])->first();
        $available = $inventory ? $inventory->quantity - $inventory->reserved_quantity : 0;

        if ($available < $validated['quantity']) {
            $product = LegumeProduct::find($validated['legume_product_id']);
            return response()->json([
                'message' => "Insufficient stock for {$product->name}. Available: {$available} kg"
            ], 400);
        }

        $item = LegumeOrderItem::create([
            'legume_order_id' => $order->id,
            'legume_product_id' => $validated['legume_product_id'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total' => $validated['quantity'] * $validated['unit_price'],
            'cost_price' => $inventory?->average_cost ?? 0,
        ]);

        // Reserve stock
        $this->inventoryService->reserveStock($validated['legume_product_id'], $validated['quantity']);

        return response()->json([
            'message' => 'Item added successfully',
            'data' => $order->fresh()->load(['customer', 'items.product'])
        ]);
    }

    /**
     * Remove item from order
     */
    public function removeItem($orderId, $itemId)
    {
        $order = LegumeOrder::findOrFail($orderId);
        $item = LegumeOrderItem::where('legume_order_id', $orderId)->findOrFail($itemId);

        if (!in_array($order->order_status, ['pending'])) {
            return response()->json([
                'message' => 'Items can only be removed from pending orders'
            ], 400);
        }

        // Release reserved stock
        $this->inventoryService->releaseReservedStock($item->legume_product_id, $item->quantity);

        $item->delete();

        return response()->json([
            'message' => 'Item removed successfully',
            'data' => $order->fresh()->load(['customer', 'items.product'])
        ]);
    }

    /**
     * Remove the specified order
     */
    public function destroy($id)
    {
        $order = LegumeOrder::findOrFail($id);

        if (!in_array($order->order_status, ['pending', 'cancelled'])) {
            return response()->json([
                'message' => 'Only pending or cancelled orders can be deleted'
            ], 400);
        }

        return DB::transaction(function () use ($order) {
            // Release any reserved stock
            if ($order->order_status === 'pending') {
                foreach ($order->items as $item) {
                    $this->inventoryService->releaseReservedStock(
                        $item->legume_product_id,
                        $item->quantity
                    );
                }
            }

            $order->delete();

            return response()->json(['message' => 'Order deleted successfully']);
        });
    }
}
