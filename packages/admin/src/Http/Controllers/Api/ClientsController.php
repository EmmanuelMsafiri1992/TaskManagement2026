<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientsController extends Controller
{
    /**
     * Display a listing of clients
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Client::query()->with('quotations');

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $clients = $query->paginate($request->input('per_page', 15));

        return response()->json($clients);
    }

    /**
     * Get client statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $stats = [
            'total' => Client::count(),
            'active' => Client::active()->count(),
            'inactive' => Client::where('status', 'inactive')->count(),
            'prospect' => Client::prospect()->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * Get all clients for dropdown/select options
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $clients = Client::select('id', 'name', 'email', 'phone', 'address', 'company_name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $clients]);
    }

    /**
     * Store a newly created client
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:191',
            'secondary_phone' => 'nullable|string|max:191',
            'company_name' => 'nullable|string|max:191',
            'business_type' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:191',
            'country' => 'nullable|string|max:191',
            'website' => 'nullable|url|max:191',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,prospect',
        ]);

        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    /**
     * Display the specified client
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $client = Client::with(['quotations' => function ($query) {
            $query->latest()->take(10);
        }])->findOrFail($id);

        return response()->json(['data' => $client]);
    }

    /**
     * Update the specified client
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:191',
            'secondary_phone' => 'nullable|string|max:191',
            'company_name' => 'nullable|string|max:191',
            'business_type' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:191',
            'country' => 'nullable|string|max:191',
            'website' => 'nullable|url|max:191',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,prospect',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    /**
     * Remove the specified client
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json(['message' => 'Client deleted successfully']);
    }
}
