<?php

namespace App\Services;

use App\Models\LegumeInventory;
use App\Models\LegumeInventoryMovement;
use App\Models\LegumeStockAlert;
use App\Models\LegumeProduct;
use Illuminate\Support\Facades\DB;

class LegumeInventoryService
{
    /**
     * Add stock from purchase (uses weighted average cost method)
     *
     * @param int $productId
     * @param float $quantity
     * @param float $unitCost
     * @param mixed $reference (Purchase model or other reference)
     * @param int|null $userId
     * @return LegumeInventory
     */
    public function addStock(int $productId, float $quantity, float $unitCost, $reference, ?int $userId = null): LegumeInventory
    {
        return DB::transaction(function () use ($productId, $quantity, $unitCost, $reference, $userId) {
            $inventory = LegumeInventory::firstOrCreate(
                ['legume_product_id' => $productId],
                ['quantity' => 0, 'average_cost' => 0, 'reserved_quantity' => 0]
            );

            // Calculate new weighted average cost
            $currentValue = $inventory->quantity * $inventory->average_cost;
            $newValue = $quantity * $unitCost;
            $newQuantity = $inventory->quantity + $quantity;
            $newAverageCost = $newQuantity > 0 ? ($currentValue + $newValue) / $newQuantity : $unitCost;

            $inventory->update([
                'quantity' => $newQuantity,
                'average_cost' => round($newAverageCost, 2),
                'last_restocked_at' => now(),
            ]);

            // Record movement
            LegumeInventoryMovement::create([
                'legume_product_id' => $productId,
                'type' => 'purchase',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'balance_after' => $newQuantity,
                'reference_type' => get_class($reference),
                'reference_id' => $reference->id,
                'user_id' => $userId ?? auth()->id(),
            ]);

            // Check for stock alerts
            $this->checkStockAlerts($productId);

            return $inventory->fresh();
        });
    }

    /**
     * Deduct stock for sale
     *
     * @param int $productId
     * @param float $quantity
     * @param mixed $reference (OrderItem model or other reference)
     * @param int|null $userId
     * @return float The cost price at time of sale (for profit calculation)
     * @throws \Exception
     */
    public function deductStock(int $productId, float $quantity, $reference, ?int $userId = null): float
    {
        return DB::transaction(function () use ($productId, $quantity, $reference, $userId) {
            $inventory = LegumeInventory::where('legume_product_id', $productId)->firstOrFail();

            $availableQuantity = $inventory->quantity - $inventory->reserved_quantity;
            if ($availableQuantity < $quantity) {
                $product = LegumeProduct::find($productId);
                throw new \Exception("Insufficient stock for {$product->name}. Available: {$availableQuantity} kg, Requested: {$quantity} kg");
            }

            $costAtSale = $inventory->average_cost;
            $newQuantity = $inventory->quantity - $quantity;

            $inventory->update(['quantity' => $newQuantity]);

            // Record movement
            LegumeInventoryMovement::create([
                'legume_product_id' => $productId,
                'type' => 'sale',
                'quantity' => -$quantity,
                'unit_cost' => $costAtSale,
                'balance_after' => $newQuantity,
                'reference_type' => get_class($reference),
                'reference_id' => $reference->id,
                'user_id' => $userId ?? auth()->id(),
            ]);

            // Check for stock alerts
            $this->checkStockAlerts($productId);

            return $costAtSale;
        });
    }

    /**
     * Reserve stock for pending order
     *
     * @param int $productId
     * @param float $quantity
     * @return bool
     * @throws \Exception
     */
    public function reserveStock(int $productId, float $quantity): bool
    {
        return DB::transaction(function () use ($productId, $quantity) {
            $inventory = LegumeInventory::where('legume_product_id', $productId)->firstOrFail();

            $availableQuantity = $inventory->quantity - $inventory->reserved_quantity;
            if ($availableQuantity < $quantity) {
                $product = LegumeProduct::find($productId);
                throw new \Exception("Insufficient stock for {$product->name}. Available: {$availableQuantity} kg");
            }

            $inventory->increment('reserved_quantity', $quantity);

            return true;
        });
    }

    /**
     * Release reserved stock
     *
     * @param int $productId
     * @param float $quantity
     * @return bool
     */
    public function releaseReservedStock(int $productId, float $quantity): bool
    {
        $inventory = LegumeInventory::where('legume_product_id', $productId)->first();

        if ($inventory) {
            $newReserved = max(0, $inventory->reserved_quantity - $quantity);
            $inventory->update(['reserved_quantity' => $newReserved]);
        }

        return true;
    }

    /**
     * Adjust stock manually
     *
     * @param int $productId
     * @param float $quantity (positive to add, negative to subtract)
     * @param string $reason
     * @param int|null $userId
     * @return LegumeInventory
     */
    public function adjustStock(int $productId, float $quantity, string $reason = '', ?int $userId = null): LegumeInventory
    {
        return DB::transaction(function () use ($productId, $quantity, $reason, $userId) {
            $inventory = LegumeInventory::firstOrCreate(
                ['legume_product_id' => $productId],
                ['quantity' => 0, 'average_cost' => 0, 'reserved_quantity' => 0]
            );

            $newQuantity = max(0, $inventory->quantity + $quantity);

            $inventory->update(['quantity' => $newQuantity]);

            // Record movement
            LegumeInventoryMovement::create([
                'legume_product_id' => $productId,
                'type' => 'adjustment',
                'quantity' => $quantity,
                'unit_cost' => $inventory->average_cost,
                'balance_after' => $newQuantity,
                'reference_type' => 'manual',
                'reference_id' => 0,
                'notes' => $reason,
                'user_id' => $userId ?? auth()->id(),
            ]);

            // Check for stock alerts
            $this->checkStockAlerts($productId);

            return $inventory->fresh();
        });
    }

    /**
     * Record damage/loss
     *
     * @param int $productId
     * @param float $quantity
     * @param string $reason
     * @param int|null $userId
     * @return LegumeInventory
     */
    public function recordDamage(int $productId, float $quantity, string $reason = '', ?int $userId = null): LegumeInventory
    {
        return DB::transaction(function () use ($productId, $quantity, $reason, $userId) {
            $inventory = LegumeInventory::where('legume_product_id', $productId)->firstOrFail();

            $newQuantity = max(0, $inventory->quantity - abs($quantity));

            $inventory->update(['quantity' => $newQuantity]);

            // Record movement
            LegumeInventoryMovement::create([
                'legume_product_id' => $productId,
                'type' => 'damage',
                'quantity' => -abs($quantity),
                'unit_cost' => $inventory->average_cost,
                'balance_after' => $newQuantity,
                'reference_type' => 'manual',
                'reference_id' => 0,
                'notes' => $reason,
                'user_id' => $userId ?? auth()->id(),
            ]);

            // Check for stock alerts
            $this->checkStockAlerts($productId);

            return $inventory->fresh();
        });
    }

    /**
     * Check and create stock alerts if needed
     *
     * @param int $productId
     * @return void
     */
    public function checkStockAlerts(int $productId): void
    {
        LegumeStockAlert::createIfNeeded($productId);
    }

    /**
     * Get low stock products
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts()
    {
        return LegumeProduct::with('inventory')
            ->active()
            ->get()
            ->filter(function ($product) {
                $currentStock = $product->inventory?->quantity ?? 0;
                return $currentStock <= $product->low_stock_threshold;
            });
    }

    /**
     * Get inventory statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $inventories = LegumeInventory::with('product')->get();

        $totalProducts = LegumeProduct::active()->count();
        $totalValue = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $inStockCount = 0;

        foreach ($inventories as $inventory) {
            $totalValue += $inventory->quantity * $inventory->average_cost;

            if ($inventory->quantity <= 0) {
                $outOfStockCount++;
            } elseif ($inventory->isLowStock()) {
                $lowStockCount++;
            } else {
                $inStockCount++;
            }
        }

        $unacknowledgedAlerts = LegumeStockAlert::unacknowledged()->count();

        return [
            'total_products' => $totalProducts,
            'total_value' => round($totalValue, 2),
            'in_stock' => $inStockCount,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
            'unacknowledged_alerts' => $unacknowledgedAlerts,
        ];
    }

    /**
     * Get movement history for a product
     *
     * @param int $productId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMovementHistory(int $productId, int $limit = 50)
    {
        return LegumeInventoryMovement::with('user')
            ->where('legume_product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
