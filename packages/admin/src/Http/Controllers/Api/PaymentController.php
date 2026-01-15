<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\ServiceProvider;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query()
            ->with(['serviceProvider', 'processedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('serviceProvider', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service_provider_id')) {
            $query->where('service_provider_id', $request->service_provider_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $payments = $query->paginate($request->get('per_page', 15));

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:bank,mobile_money,cash,cheque',
            'reference_number' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'status' => 'required|in:pending,completed,failed,cancelled',
            'month_for' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['processed_by'] = $request->user()->id;

        $payment = Payment::create($validated);

        return response()->json([
            'message' => 'Payment recorded successfully',
            'data' => $payment->load(['serviceProvider', 'processedBy']),
        ], 201);
    }

    public function show(Payment $payment)
    {
        $payment->load(['serviceProvider', 'processedBy']);

        return response()->json([
            'data' => $payment,
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_method' => 'sometimes|in:bank,mobile_money,cash,cheque',
            'reference_number' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'payment_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,completed,failed,cancelled',
            'month_for' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $payment->fresh(['serviceProvider', 'processedBy']),
        ]);
    }

    public function destroy(Payment $payment)
    {
        // If the payment was completed, we need to subtract from total_paid
        if ($payment->status === 'completed') {
            $payment->serviceProvider->decrement('total_paid', $payment->amount);
        }

        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully',
        ]);
    }

    public function statistics(Request $request)
    {
        $query = Payment::query();

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $stats = [
            'total_payments' => (clone $query)->count(),
            'completed_payments' => (clone $query)->where('status', 'completed')->count(),
            'pending_payments' => (clone $query)->where('status', 'pending')->count(),
            'total_amount_paid' => (clone $query)->where('status', 'completed')->sum('amount'),
            'total_pending_amount' => (clone $query)->where('status', 'pending')->sum('amount'),
            'payments_by_method' => [
                'bank' => (clone $query)->where('payment_method', 'bank')->where('status', 'completed')->sum('amount'),
                'mobile_money' => (clone $query)->where('payment_method', 'mobile_money')->where('status', 'completed')->sum('amount'),
                'cash' => (clone $query)->where('payment_method', 'cash')->where('status', 'completed')->sum('amount'),
                'cheque' => (clone $query)->where('payment_method', 'cheque')->where('status', 'completed')->sum('amount'),
            ],
            'total_service_providers' => ServiceProvider::count(),
            'total_agreed_amount' => ServiceProvider::sum('total_agreed_amount'),
            'total_balance_remaining' => ServiceProvider::sum('total_agreed_amount') - ServiceProvider::sum('total_paid'),
        ];

        return response()->json($stats);
    }

    public function providerPayments(ServiceProvider $serviceProvider, Request $request)
    {
        $payments = $serviceProvider->payments()
            ->with('processedBy')
            ->orderBy('payment_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($payments);
    }

    public function generateReceipt(Payment $payment)
    {
        $payment->load(['serviceProvider', 'processedBy']);

        $data = [
            'payment' => $payment,
            'company' => [
                'name' => 'Studyseco.com',
                'address' => 'Blantyre, Malawi',
                'phone' => '+265 999 000 000',
                'email' => 'info@studyseco.com',
            ],
            'generated_at' => now()->format('F d, Y H:i'),
        ];

        $pdf = Pdf::loadView('receipts.payment', $data);

        return $pdf->download("receipt-{$payment->receipt_number}.pdf");
    }

    public function markAsCompleted(Payment $payment, Request $request)
    {
        if ($payment->status === 'completed') {
            return response()->json([
                'message' => 'Payment is already completed',
            ], 400);
        }

        $payment->update([
            'status' => 'completed',
            'processed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Payment marked as completed',
            'data' => $payment->fresh(['serviceProvider', 'processedBy']),
        ]);
    }

    public function bulkPayment(Request $request)
    {
        $validated = $request->validate([
            'service_provider_ids' => 'required|array',
            'service_provider_ids.*' => 'exists:service_providers,id',
            'payment_date' => 'required|date',
            'month_for' => 'required|string|max:50',
            'payment_method' => 'required|in:bank,mobile_money,cash,cheque',
            'notes' => 'nullable|string',
        ]);

        $payments = [];
        foreach ($validated['service_provider_ids'] as $providerId) {
            $provider = ServiceProvider::find($providerId);

            // Use monthly amount if set, otherwise calculate
            $amount = $provider->monthly_amount ?? ($provider->total_agreed_amount / 12);

            $payment = Payment::create([
                'service_provider_id' => $providerId,
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'status' => 'pending',
                'month_for' => $validated['month_for'],
                'notes' => $validated['notes'] ?? null,
                'processed_by' => $request->user()->id,
            ]);

            $payments[] = $payment;
        }

        return response()->json([
            'message' => count($payments) . ' payments created successfully',
            'data' => $payments,
        ], 201);
    }
}
