<?php

namespace Admin\Http\Controllers\Api;

use App\Models\GeneratorFuel;
use App\Models\GeneratorFuelLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GeneratorFuelController
{
    /**
     * Get generator status and current fuel level.
     */
    public function status()
    {
        $generator = GeneratorFuel::first();

        if (!$generator) {
            // Create default generator record if none exists
            $generator = GeneratorFuel::create([
                'current_level' => 0,
                'tank_capacity' => 12,
                'reserve_fuel' => 0.5,
                'consumption_rate' => 1,
                'is_running' => false,
            ]);
        }

        // Calculate consumed fuel if running
        if ($generator->is_running && $generator->last_started_at) {
            $hoursRunning = Carbon::parse($generator->last_started_at)->diffInMinutes(now()) / 60;
            $consumed = $hoursRunning * $generator->consumption_rate;
            $generator->current_level = max(0, $generator->current_level - $consumed);
        }

        return [
            'generator' => $generator,
            'fuel_percentage' => $generator->fuel_percentage,
            'is_low_fuel' => $generator->is_low_fuel,
        ];
    }

    /**
     * Get fuel logs history.
     */
    public function logs(Request $request)
    {
        $query = GeneratorFuelLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query->paginate($request->per_page ?? 20);
    }

    /**
     * Add fuel to the generator.
     */
    public function addFuel(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string|max:500',
        ]);

        $generator = GeneratorFuel::first();

        if (!$generator) {
            return response()->json(['message' => 'Generator not found'], 404);
        }

        $levelBefore = $generator->current_level;
        $newLevel = min($generator->tank_capacity, $levelBefore + $request->amount);

        $generator->update([
            'current_level' => $newLevel,
            'last_updated_at' => now(),
        ]);

        GeneratorFuelLog::create([
            'user_id' => auth()->id(),
            'action' => 'refuel',
            'amount' => $request->amount,
            'level_before' => $levelBefore,
            'level_after' => $newLevel,
            'notes' => $request->notes,
        ]);

        return [
            'message' => 'Fuel added successfully',
            'generator' => $generator->fresh(),
        ];
    }

    /**
     * Start the generator.
     */
    public function start()
    {
        $generator = GeneratorFuel::first();

        if (!$generator) {
            return response()->json(['message' => 'Generator not found'], 404);
        }

        if ($generator->is_running) {
            return response()->json(['message' => 'Generator is already running'], 400);
        }

        if ($generator->current_level <= 0) {
            return response()->json(['message' => 'No fuel available'], 400);
        }

        $generator->update([
            'is_running' => true,
            'last_started_at' => now(),
        ]);

        GeneratorFuelLog::create([
            'user_id' => auth()->id(),
            'action' => 'start',
            'amount' => 0,
            'level_before' => $generator->current_level,
            'level_after' => $generator->current_level,
            'notes' => 'Generator started',
        ]);

        return [
            'message' => 'Generator started',
            'generator' => $generator->fresh(),
        ];
    }

    /**
     * Stop the generator.
     */
    public function stop()
    {
        $generator = GeneratorFuel::first();

        if (!$generator) {
            return response()->json(['message' => 'Generator not found'], 404);
        }

        if (!$generator->is_running) {
            return response()->json(['message' => 'Generator is not running'], 400);
        }

        // Calculate consumed fuel
        $hoursRunning = 0;
        $consumed = 0;
        $levelBefore = $generator->current_level;

        if ($generator->last_started_at) {
            $hoursRunning = Carbon::parse($generator->last_started_at)->diffInMinutes(now()) / 60;
            $consumed = $hoursRunning * $generator->consumption_rate;
        }

        $newLevel = max(0, $levelBefore - $consumed);

        $generator->update([
            'is_running' => false,
            'current_level' => $newLevel,
            'last_stopped_at' => now(),
            'last_updated_at' => now(),
        ]);

        GeneratorFuelLog::create([
            'user_id' => auth()->id(),
            'action' => 'stop',
            'amount' => $consumed,
            'level_before' => $levelBefore,
            'level_after' => $newLevel,
            'notes' => sprintf('Generator stopped after %.1f hours, consumed %.2f liters', $hoursRunning, $consumed),
        ]);

        return [
            'message' => 'Generator stopped',
            'generator' => $generator->fresh(),
            'consumed' => round($consumed, 2),
            'hours_running' => round($hoursRunning, 2),
        ];
    }

    /**
     * Update generator settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'tank_capacity' => 'required|numeric|min:1',
            'reserve_fuel' => 'required|numeric|min:0',
            'consumption_rate' => 'required|numeric|min:0.1',
        ]);

        $generator = GeneratorFuel::first();

        if (!$generator) {
            $generator = GeneratorFuel::create([
                'current_level' => 0,
                'tank_capacity' => $request->tank_capacity,
                'reserve_fuel' => $request->reserve_fuel,
                'consumption_rate' => $request->consumption_rate,
                'is_running' => false,
            ]);
        } else {
            $generator->update([
                'tank_capacity' => $request->tank_capacity,
                'reserve_fuel' => $request->reserve_fuel,
                'consumption_rate' => $request->consumption_rate,
            ]);
        }

        return [
            'message' => 'Settings updated successfully',
            'generator' => $generator->fresh(),
        ];
    }

    /**
     * Get usage statistics.
     */
    public function statistics()
    {
        $generator = GeneratorFuel::first();

        // Get logs for this month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyRefuels = GeneratorFuelLog::where('action', 'refuel')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyConsumption = GeneratorFuelLog::where('action', 'stop')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalRefuels = GeneratorFuelLog::where('action', 'refuel')->sum('amount');
        $totalConsumption = GeneratorFuelLog::where('action', 'stop')->sum('amount');

        return [
            'current_level' => $generator ? $generator->current_level : 0,
            'tank_capacity' => $generator ? $generator->tank_capacity : 12,
            'monthly_refuels' => round($monthlyRefuels, 2),
            'monthly_consumption' => round($monthlyConsumption, 2),
            'total_refuels' => round($totalRefuels, 2),
            'total_consumption' => round($totalConsumption, 2),
        ];
    }
}
