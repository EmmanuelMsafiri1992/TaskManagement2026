<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegumeOrderPayment;
use App\Models\LegumeOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegumeOrderPaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = LegumeOrderPayment::query()->with(['order.customer', 'user']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('payment_method')) {
            $query->paymentMethod($request->payment_method);
        }

        if ($request->filled('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }

        $sortBy = $request->input('sort_by', 'payment_date');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $payments = $query->paginate($request->input('per_page', 15));

        return response()->json($payments);
    }

    /**
     * Get payment statistics
     */
    public function statistics()
    {
        $totalPayments = LegumeOrderPayment::completed()->sum('amount');
        $pendingPayments = LegumeOrderPayment::pending()->sum('amount');

        // By payment method
        $byMethod = LegumeOrderPayment::completed()
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        // This month
        $thisMonthTotal = LegumeOrderPayment::completed()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // Today
        $todayTotal = LegumeOrderPayment::completed()
            ->whereDate('payment_date', now()->toDateString())
            ->sum('amount');

        return response()->json([
            'data' => [
                'total_received' => round($totalPayments, 2),
                'pending_payments' => round($pendingPayments, 2),
                'this_month' => round($thisMonthTotal, 2),
                'today' => round($todayTotal, 2),
                'by_method' => [
                    'cash' => round($byMethod['cash']->total ?? 0, 2),
                    'bank_transfer' => round($byMethod['bank_transfer']->total ?? 0, 2),
                    'airtel_money' => round($byMethod['airtel_money']->total ?? 0, 2),
                    'tnm_mpamba' => round($byMethod['tnm_mpamba']->total ?? 0, 2),
                    'other' => round($byMethod['other']->total ?? 0, 2),
                ],
            ]
        ]);
    }

    /**
     * Store a new payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'legume_order_id' => 'required|exists:legume_orders,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,airtel_money,tnm_mpamba,other',
            'transaction_id' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,completed',
        ]);

        $order = LegumeOrder::findOrFail($validated['legume_order_id']);

        // Check if order can receive payments
        if ($order->order_status === 'cancelled') {
            return response()->json([
                'message' => 'Cannot add payment to a cancelled order'
            ], 400);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'This order is already fully paid'
            ], 400);
        }

        // Check if amount exceeds balance
        $balance = $order->total_amount - $order->amount_paid;
        if ($validated['amount'] > $balance) {
            return response()->json([
                'message' => "Payment amount exceeds balance due. Balance: MWK " . number_format($balance, 2)
            ], 400);
        }

        $payment = LegumeOrderPayment::create([
            'legume_order_id' => $validated['legume_order_id'],
            'user_id' => auth()->id(),
            'amount' => $validated['amount'],
            'currency' => 'MWK',
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'payment_date' => $validated['payment_date'],
            'status' => $validated['status'] ?? 'completed',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Payment recorded successfully',
            'data' => $payment->load(['order.customer', 'user']),
            'order' => $order->fresh(),
        ], 201);
    }

    /**
     * Display the specified payment
     */
    public function show($id)
    {
        $payment = LegumeOrderPayment::with(['order.customer', 'user'])->findOrFail($id);

        return response()->json(['data' => $payment]);
    }

    /**
     * Get payments for a specific order
     */
    public function forOrder($orderId)
    {
        $order = LegumeOrder::findOrFail($orderId);

        $payments = $order->payments()
            ->with('user')
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'data' => $payments,
            'order_total' => $order->total_amount,
            'amount_paid' => $order->amount_paid,
            'balance_due' => $order->balance_due,
        ]);
    }

    /**
     * Mark payment as completed
     */
    public function markCompleted($id)
    {
        $payment = LegumeOrderPayment::findOrFail($id);

        if ($payment->status === 'completed') {
            return response()->json([
                'message' => 'Payment is already completed'
            ], 400);
        }

        $payment->update(['status' => 'completed']);

        return response()->json([
            'message' => 'Payment marked as completed',
            'data' => $payment->fresh()->load(['order.customer', 'user'])
        ]);
    }

    /**
     * Reverse a payment
     */
    public function reverse($id)
    {
        $payment = LegumeOrderPayment::findOrFail($id);

        if ($payment->status === 'reversed') {
            return response()->json([
                'message' => 'Payment is already reversed'
            ], 400);
        }

        $payment->update(['status' => 'reversed']);

        return response()->json([
            'message' => 'Payment reversed successfully',
            'data' => $payment->fresh()->load(['order.customer', 'user'])
        ]);
    }

    /**
     * Verify mobile money transaction (placeholder)
     */
    public function verifyMobileMoney(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'payment_method' => 'required|in:airtel_money,tnm_mpamba',
        ]);

        // This is a placeholder for actual mobile money API integration
        // In production, this would call the Airtel Money or TNM Mpamba API
        // to verify the transaction

        return response()->json([
            'message' => 'Mobile money verification is not yet integrated. Please verify manually.',
            'verified' => false,
            'data' => [
                'transaction_id' => $validated['transaction_id'],
                'payment_method' => $validated['payment_method'],
            ]
        ]);
    }

    /**
     * Remove the specified payment
     */
    public function destroy($id)
    {
        $payment = LegumeOrderPayment::findOrFail($id);

        if ($payment->status === 'completed') {
            return response()->json([
                'message' => 'Completed payments cannot be deleted. Please reverse the payment first.'
            ], 400);
        }

        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
