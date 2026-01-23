<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['assignedUser:id,name,email,avatar', 'createdBy:id,name']);

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by condition
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $items = $query->paginate($perPage);

        // Get filter options
        $categories = Inventory::whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $locations = Inventory::whereNotNull('location')
            ->distinct()
            ->pluck('location');

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'filters' => [
                'categories' => $categories,
                'locations' => $locations,
                'statuses' => ['available', 'in_use', 'maintenance', 'retired'],
                'conditions' => ['excellent', 'good', 'fair', 'poor', 'damaged'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:191',
            'item_code' => 'nullable|string|max:191|unique:inventory,item_code',
            'category' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|in:excellent,good,fair,poor,damaged',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'supplier' => 'nullable|string|max:191',
            'location' => 'nullable|string|max:191',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'required|in:available,in_use,maintenance,retired',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['currency'] = $validated['currency'] ?? 'MWK';

        // Generate item code if not provided
        if (empty($validated['item_code'])) {
            $validated['item_code'] = 'INV-' . strtoupper(substr(uniqid(), -6));
        }

        $item = Inventory::create($validated);
        $item->load(['assignedUser:id,name,email,avatar', 'createdBy:id,name']);

        return response()->json([
            'message' => 'Inventory item created successfully',
            'data' => $item,
        ], 201);
    }

    public function show($id)
    {
        $item = Inventory::with([
            'assignedUser:id,name,email,avatar',
            'createdBy:id,name'
        ])->findOrFail($id);

        return response()->json(['data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:191',
            'item_code' => 'nullable|string|max:191|unique:inventory,item_code,' . $id,
            'category' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'condition' => 'required|in:excellent,good,fair,poor,damaged',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'supplier' => 'nullable|string|max:191',
            'location' => 'nullable|string|max:191',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'status' => 'required|in:available,in_use,maintenance,retired',
        ]);

        $item->update($validated);
        $item->load(['assignedUser:id,name,email,avatar', 'createdBy:id,name']);

        return response()->json([
            'message' => 'Inventory item updated successfully',
            'data' => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Inventory item deleted successfully',
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_items' => Inventory::count(),
            'total_quantity' => Inventory::sum('quantity'),
            'total_value' => Inventory::sum(DB::raw('quantity * purchase_price')),
            'available' => Inventory::where('status', 'available')->count(),
            'in_use' => Inventory::where('status', 'in_use')->count(),
            'maintenance' => Inventory::where('status', 'maintenance')->count(),
            'retired' => Inventory::where('status', 'retired')->count(),
            'by_category' => Inventory::select('category', DB::raw('count(*) as count'), DB::raw('sum(quantity) as total_quantity'))
                ->whereNotNull('category')
                ->groupBy('category')
                ->get(),
            'by_condition' => Inventory::select('condition', DB::raw('count(*) as count'))
                ->groupBy('condition')
                ->get(),
        ];

        return response()->json(['data' => $stats]);
    }

    public function users()
    {
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $users]);
    }

    /**
     * Upload images for an inventory item.
     */
    public function uploadImages(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $existingPaths = $item->image_paths ?? [];
        $newPaths = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('inventory-images', 'public');
            $newPaths[] = $path;
        }

        $item->update([
            'image_paths' => array_merge($existingPaths, $newPaths),
        ]);

        return response()->json([
            'message' => 'Images uploaded successfully',
            'data' => $item->fresh(),
        ]);
    }

    /**
     * Delete an image from an inventory item.
     */
    public function deleteImage(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);

        $request->validate([
            'image_path' => 'required|string',
        ]);

        $imagePath = $request->image_path;
        $existingPaths = $item->image_paths ?? [];

        if (in_array($imagePath, $existingPaths)) {
            // Delete the file from storage
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Remove from array
            $updatedPaths = array_values(array_filter($existingPaths, fn($path) => $path !== $imagePath));

            $item->update([
                'image_paths' => $updatedPaths,
            ]);
        }

        return response()->json([
            'message' => 'Image deleted successfully',
            'data' => $item->fresh(),
        ]);
    }
}
