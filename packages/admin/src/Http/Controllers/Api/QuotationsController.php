<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
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
        $query = Quotation::query()->with(['user', 'items', 'project', 'client', 'company']);

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
            'project_id' => 'nullable|exists:projects,id',
            'company_id' => 'nullable|exists:companies,id',
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
            'business_name' => 'nullable|string|max:191',
            'business_email' => 'nullable|string|max:191',
            'business_phone' => 'nullable|string|max:191',
            'business_address' => 'nullable|string',
            'logo' => 'nullable|string|max:191',
            'color' => 'nullable|string|max:20',
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

            // Set default values for nullable fields that cannot be null in database
            $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
            $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

            $validated['subtotal'] = $subtotal;
            $validated['tax_amount'] = $validated['tax_rate'] / 100 * $subtotal;
            $validated['total_amount'] = $subtotal + $validated['tax_amount'] - $validated['discount_amount'];

            // Remove items from validated data before creating quotation (items are handled separately)
            $items = $validated['items'];
            unset($validated['items']);

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
        $quotation = Quotation::with(['user', 'items', 'project', 'client', 'company'])->findOrFail($id);

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
            'project_id' => 'nullable|exists:projects,id',
            'company_id' => 'nullable|exists:companies,id',
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
            'business_name' => 'nullable|string|max:191',
            'business_email' => 'nullable|string|max:191',
            'business_phone' => 'nullable|string|max:191',
            'business_address' => 'nullable|string',
            'logo' => 'nullable|string|max:191',
            'color' => 'nullable|string|max:20',
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

            // Set default values for nullable fields that cannot be null in database
            $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
            $validated['discount_amount'] = $validated['discount_amount'] ?? 0;

            $validated['subtotal'] = $subtotal;
            $validated['tax_amount'] = $validated['tax_rate'] / 100 * $subtotal;
            $validated['total_amount'] = $subtotal + $validated['tax_amount'] - $validated['discount_amount'];

            // Remove items from validated data before updating quotation (items are handled separately)
            unset($validated['items']);

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

    /**
     * Get list of projects for quotation selection
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function projects(Request $request)
    {
        $query = Project::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%');
        }

        $projects = $query->select(['id', 'name', 'company_name', 'company_email', 'company_phone', 'company_address', 'company_logo'])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $projects]);
    }

    /**
     * Get project details for auto-populating quotation business fields
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function projectDetails($id)
    {
        $project = Project::select(['id', 'name', 'company_name', 'company_email', 'company_phone', 'company_address', 'company_logo'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'business_name' => $project->company_name,
                'business_email' => $project->company_email,
                'business_phone' => $project->company_phone,
                'business_address' => $project->company_address,
                'logo' => $project->company_logo,
            ]
        ]);
    }
}
