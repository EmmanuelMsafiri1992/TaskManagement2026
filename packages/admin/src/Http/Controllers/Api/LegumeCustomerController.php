<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumeCustomer;
use Illuminate\Http\Request;

class LegumeCustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = LegumeCustomer::query()->withCount('orders');

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

        $customers = $query->paginate($request->input('per_page', 15));

        return response()->json($customers);
    }

    /**
     * Get customer statistics
     */
    public function statistics()
    {
        $totalCustomers = LegumeCustomer::count();
        $activeCustomers = LegumeCustomer::active()->count();
        $totalRevenue = LegumeCustomer::sum('total_purchases');

        // Top customers by total purchases
        $topCustomers = LegumeCustomer::orderBy('total_purchases', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'total_purchases', 'district']);

        // Districts
        $districts = LegumeCustomer::whereNotNull('district')
            ->distinct()
            ->pluck('district');

        // New customers this month
        $newThisMonth = LegumeCustomer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return response()->json([
            'data' => [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'total_revenue' => round($totalRevenue, 2),
                'new_this_month' => $newThisMonth,
                'top_customers' => $topCustomers,
                'districts' => $districts,
            ]
        ]);
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'delivery_notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';
        $validated['total_purchases'] = 0;

        $customer = LegumeCustomer::create($validated);

        return response()->json($customer, 201);
    }

    /**
     * Display the specified customer
     */
    public function show($id)
    {
        $customer = LegumeCustomer::withCount('orders')->findOrFail($id);

        // Get recent orders
        $recentOrders = $customer->orders()
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => $customer,
            'recent_orders' => $recentOrders,
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        $customer = LegumeCustomer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'delivery_notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    /**
     * Get order history for a customer
     */
    public function orderHistory($id)
    {
        $customer = LegumeCustomer::findOrFail($id);

        $orders = $customer->orders()
            ->with('items.product:id,name,sku')
            ->orderBy('order_date', 'desc')
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Remove the specified customer
     */
    public function destroy($id)
    {
        $customer = LegumeCustomer::findOrFail($id);

        if ($customer->orders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete customer with existing orders. Consider marking as inactive instead.'
            ], 400);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }

    /**
     * Get all customers for dropdown
     */
    public function dropdown()
    {
        $customers = LegumeCustomer::active()
            ->select('id', 'name', 'phone', 'address', 'city', 'district')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $customers]);
    }
}
