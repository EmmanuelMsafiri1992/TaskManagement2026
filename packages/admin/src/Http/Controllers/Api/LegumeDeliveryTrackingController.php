<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumeDeliveryTracking;
use App\Models\LegumeOrder;
use Illuminate\Http\Request;

class LegumeDeliveryTrackingController extends Controller
{
    /**
     * Display a listing of deliveries
     */
    public function index(Request $request)
    {
        $query = LegumeDeliveryTracking::query()->with(['order.customer', 'user']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $deliveries = $query->paginate($request->input('per_page', 15));

        return response()->json($deliveries);
    }

    /**
     * Get delivery statistics
     */
    public function statistics()
    {
        $totalDeliveries = LegumeDeliveryTracking::count();
        $pendingDeliveries = LegumeDeliveryTracking::pending()->count();
        $inTransitDeliveries = LegumeDeliveryTracking::inTransit()->count();
        $completedDeliveries = LegumeDeliveryTracking::delivered()->count();
        $failedDeliveries = LegumeDeliveryTracking::failed()->count();

        $totalDeliveryCost = LegumeDeliveryTracking::delivered()->sum('delivery_cost');

        // Today
        $todayDeliveries = LegumeDeliveryTracking::whereDate('created_at', now()->toDateString())->count();
        $todayCompleted = LegumeDeliveryTracking::whereDate('actual_delivery', now()->toDateString())->count();

        return response()->json([
            'data' => [
                'total_deliveries' => $totalDeliveries,
                'pending' => $pendingDeliveries,
                'in_transit' => $inTransitDeliveries,
                'completed' => $completedDeliveries,
                'failed' => $failedDeliveries,
                'total_delivery_cost' => round($totalDeliveryCost, 2),
                'today_deliveries' => $todayDeliveries,
                'today_completed' => $todayCompleted,
            ]
        ]);
    }

    /**
     * Store a new delivery
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'legume_order_id' => 'required|exists:legume_orders,id',
            'driver_name' => 'nullable|string|max:191',
            'driver_phone' => 'nullable|string|max:20',
            'vehicle_info' => 'nullable|string|max:191',
            'delivery_address' => 'required|string',
            'delivery_cost' => 'nullable|numeric|min:0',
            'estimated_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $order = LegumeOrder::findOrFail($validated['legume_order_id']);

        // Check if delivery already exists
        if ($order->delivery) {
            return response()->json([
                'message' => 'Delivery tracking already exists for this order'
            ], 400);
        }

        // Check if order is for delivery
        if ($order->fulfillment_type !== 'delivery') {
            return response()->json([
                'message' => 'This order is for pickup, not delivery'
            ], 400);
        }

        $delivery = LegumeDeliveryTracking::create([
            'legume_order_id' => $validated['legume_order_id'],
            'user_id' => auth()->id(),
            'status' => 'pending',
            'driver_name' => $validated['driver_name'] ?? null,
            'driver_phone' => $validated['driver_phone'] ?? null,
            'vehicle_info' => $validated['vehicle_info'] ?? null,
            'delivery_address' => $validated['delivery_address'],
            'delivery_cost' => $validated['delivery_cost'] ?? 0,
            'estimated_delivery' => $validated['estimated_delivery'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update order delivery fee if provided
        if (isset($validated['delivery_cost']) && $validated['delivery_cost'] > 0) {
            $order->update(['delivery_fee' => $validated['delivery_cost']]);
            $order->calculateTotals();
        }

        return response()->json($delivery->load(['order.customer', 'user']), 201);
    }

    /**
     * Display the specified delivery
     */
    public function show($id)
    {
        $delivery = LegumeDeliveryTracking::with(['order.customer', 'order.items.product', 'user'])
            ->findOrFail($id);

        return response()->json(['data' => $delivery]);
    }

    /**
     * Update the specified delivery
     */
    public function update(Request $request, $id)
    {
        $delivery = LegumeDeliveryTracking::findOrFail($id);

        $validated = $request->validate([
            'driver_name' => 'nullable|string|max:191',
            'driver_phone' => 'nullable|string|max:20',
            'vehicle_info' => 'nullable|string|max:191',
            'delivery_address' => 'required|string',
            'delivery_cost' => 'nullable|numeric|min:0',
            'estimated_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $delivery->update($validated);

        return response()->json($delivery->load(['order.customer', 'user']));
    }

    /**
     * Update delivery status
     */
    public function updateStatus(Request $request, $id)
    {
        $delivery = LegumeDeliveryTracking::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,picked_up,in_transit,delivered,failed',
            'failure_reason' => 'nullable|required_if:status,failed|string',
            'driver_name' => 'nullable|string|max:191',
            'driver_phone' => 'nullable|string|max:20',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'delivered') {
            $updateData['actual_delivery'] = now();

            // Update order status
            $delivery->order->update([
                'order_status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }

        if ($validated['status'] === 'failed') {
            $updateData['failure_reason'] = $validated['failure_reason'] ?? null;
        }

        if ($validated['status'] === 'assigned') {
            if (isset($validated['driver_name'])) {
                $updateData['driver_name'] = $validated['driver_name'];
            }
            if (isset($validated['driver_phone'])) {
                $updateData['driver_phone'] = $validated['driver_phone'];
            }
        }

        $delivery->update($updateData);

        return response()->json([
            'message' => 'Delivery status updated successfully',
            'data' => $delivery->fresh()->load(['order.customer', 'user'])
        ]);
    }

    /**
     * Get delivery for a specific order
     */
    public function forOrder($orderId)
    {
        $order = LegumeOrder::findOrFail($orderId);
        $delivery = $order->delivery;

        if (!$delivery) {
            return response()->json([
                'message' => 'No delivery tracking found for this order',
                'data' => null
            ]);
        }

        return response()->json(['data' => $delivery->load('user')]);
    }

    /**
     * Remove the specified delivery
     */
    public function destroy($id)
    {
        $delivery = LegumeDeliveryTracking::findOrFail($id);

        if ($delivery->status === 'delivered') {
            return response()->json([
                'message' => 'Completed deliveries cannot be deleted'
            ], 400);
        }

        $delivery->delete();

        return response()->json(['message' => 'Delivery tracking deleted successfully']);
    }
}
