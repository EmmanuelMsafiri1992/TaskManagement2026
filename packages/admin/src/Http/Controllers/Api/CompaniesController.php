<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompaniesController extends Controller
{
    /**
     * Display a listing of companies.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Company::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by industry
        if ($request->filled('industry')) {
            $query->where('industry', $request->industry);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $companies = $query->paginate($request->input('per_page', 15));

        return response()->json($companies);
    }

    /**
     * Get company statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total' => Company::count(),
            'active' => Company::active()->count(),
            'inactive' => Company::inactive()->count(),
            'with_projects' => Company::has('projects')->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Get company options for dropdowns.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $companies = Company::active()
            ->select(['id', 'name', 'trading_name', 'email', 'phone', 'address', 'city', 'country', 'logo'])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $companies]);
    }

    /**
     * Store a newly created company.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'trading_name' => 'nullable|string|max:191',
            'registration_number' => 'nullable|string|max:191',
            'tax_number' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:191',
            'secondary_phone' => 'nullable|string|max:191',
            'fax' => 'nullable|string|max:191',
            'website' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'postal_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:191',
            'logo' => 'nullable|string|max:191',
            'industry' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'bank_name' => 'nullable|string|max:191',
            'bank_account_name' => 'nullable|string|max:191',
            'bank_account_number' => 'nullable|string|max:191',
            'bank_branch' => 'nullable|string|max:191',
            'bank_swift_code' => 'nullable|string|max:191',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $company = Company::create($validated);

        return response()->json($company, 201);
    }

    /**
     * Display the specified company.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $company = Company::with(['projects'])->findOrFail($id);

        return response()->json(['data' => $company]);
    }

    /**
     * Update the specified company.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'trading_name' => 'nullable|string|max:191',
            'registration_number' => 'nullable|string|max:191',
            'tax_number' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:191',
            'secondary_phone' => 'nullable|string|max:191',
            'fax' => 'nullable|string|max:191',
            'website' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'postal_code' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:191',
            'logo' => 'nullable|string|max:191',
            'industry' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'bank_name' => 'nullable|string|max:191',
            'bank_account_name' => 'nullable|string|max:191',
            'bank_account_number' => 'nullable|string|max:191',
            'bank_branch' => 'nullable|string|max:191',
            'bank_swift_code' => 'nullable|string|max:191',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $company->update($validated);

        return response()->json($company);
    }

    /**
     * Remove the specified company.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return response()->json(['message' => 'Company deleted successfully']);
    }

    /**
     * Upload company logo.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadLogo(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old logo if exists
        if ($company->logo && Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        $path = $request->file('logo')->store('company-logos', 'public');
        $company->update(['logo' => $path]);

        return response()->json([
            'message' => 'Logo uploaded successfully',
            'logo' => $path,
        ]);
    }

    /**
     * Get company details for quotation auto-fill.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function quotationDetails($id)
    {
        $company = Company::select([
            'id', 'name', 'trading_name', 'email', 'phone', 'address',
            'city', 'state', 'postal_code', 'country', 'logo'
        ])->findOrFail($id);

        // Format full address
        $fullAddress = collect([
            $company->address,
            $company->city,
            $company->state,
            $company->postal_code,
            $company->country,
        ])->filter()->implode(', ');

        return response()->json([
            'data' => [
                'id' => $company->id,
                'name' => $company->name,
                'business_name' => $company->trading_name ?: $company->name,
                'business_email' => $company->email,
                'business_phone' => $company->phone,
                'business_address' => $fullAddress ?: $company->address,
                'logo' => $company->logo,
            ]
        ]);
    }
}
