<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationsController extends Controller
{
    /**
     * Display a listing of quotations
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Quotation::query()->with(['user', 'items']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('quotation_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('quotation_date', '<=', $request->end_date);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $quotations = $query->paginate($request->input('per_page', 15));

        return response()->json($quotations);
    }

    /**
     * Get quotation statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total' => Quotation::count(),
            'draft' => Quotation::status('draft')->count(),
            'sent' => Quotation::status('sent')->count(),
            'accepted' => Quotation::status('accepted')->count(),
            'rejected' => Quotation::status('rejected')->count(),
            'expired' => Quotation::status('expired')->count(),
            'total_value' => Quotation::sum('total_amount'),
            'accepted_value' => Quotation::status('accepted')->sum('total_amount'),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Store a newly created quotation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'customer_name' => 'required|string|max:191',
            'customer_email' => 'required|email|max:191',
            'customer_phone' => 'nullable|string|max:191',
            'customer_address' => 'nullable|string',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:quotation_date',
            'currency' => 'required|string|max:10',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'template' => 'required|string|max:191',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate quotation number
            $validated['quotation_number'] = Quotation::generateQuotationNumber();
            $validated['user_id'] = auth()->id();
            $validated['status'] = $validated['status'] ?? 'draft';

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $validated['subtotal'] = $subtotal;
            $validated['tax_amount'] = ($validated['tax_rate'] ?? 0) / 100 * $subtotal;
            $validated['total_amount'] = $subtotal + $validated['tax_amount'] - ($validated['discount_amount'] ?? 0);

            // Create quotation
            $quotation = Quotation::create($validated);

            // Create quotation items
            foreach ($request->items as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            return response()->json($quotation->load('items'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Quotation creation failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'message' => 'Failed to create quotation',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Display the specified quotation
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $quotation = Quotation::with(['user', 'items'])->findOrFail($id);

        return response()->json(['data' => $quotation]);
    }

    /**
     * Update the specified quotation
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'customer_name' => 'required|string|max:191',
            'customer_email' => 'required|email|max:191',
            'customer_phone' => 'nullable|string|max:191',
            'customer_address' => 'nullable|string',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:quotation_date',
            'currency' => 'required|string|max:10',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'template' => 'required|string|max:191',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'status' => 'nullable|in:draft,sent,accepted,rejected,expired',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $validated['subtotal'] = $subtotal;
            $validated['tax_amount'] = ($validated['tax_rate'] ?? 0) / 100 * $subtotal;
            $validated['total_amount'] = $subtotal + $validated['tax_amount'] - ($validated['discount_amount'] ?? 0);

            // Update quotation
            $quotation->update($validated);

            // Delete old items and create new ones
            $quotation->items()->delete();
            foreach ($request->items as $index => $item) {
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            return response()->json($quotation->load('items'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update quotation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Change quotation status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, $id)
    {
        $quotation = Quotation::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:draft,sent,accepted,rejected,expired',
        ]);

        $quotation->update($validated);

        return response()->json(['message' => 'Quotation status updated successfully', 'data' => $quotation]);
    }

    /**
     * Remove the specified quotation
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);

        DB::beginTransaction();
        try {
            $quotation->items()->delete();
            $quotation->delete();

            DB::commit();

            return response()->json(['message' => 'Quotation deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete quotation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate PDF for the specified quotation
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function pdf($id)
    {
        $quotation = Quotation::with(['user', 'items'])->findOrFail($id);

        $pdf = Pdf::loadView('quotations.pdf', ['quotation' => $quotation]);

        return $pdf->download($quotation->quotation_number . '.pdf');
    }
}
