<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Requests\UserWorkingHoursRequest;
use App\Models\User;
use App\Models\UserWorkingHours;
use AhsanDev\Support\Field;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserWorkingHoursController
{
    /**
     * Check if the current user can manage working hours.
     */
    protected function canManage(): bool
    {
        $user = auth()->user();
        return $user->email === 'emmanuel@emphxs.com' || $user->isSuperAdmin();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        $query = UserWorkingHours::with(['user', 'assignedBy']);

        // Filter by user
        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'current') {
                $query->current();
            } elseif ($request->status === 'expired') {
                $query->where('is_active', true)
                    ->whereNotNull('effective_until')
                    ->where('effective_until', '<', Carbon::today());
            }
        }

        // Search by user name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(UserWorkingHours $workingHours)
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        return $this->fields($workingHours);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, UserWorkingHours $workingHours)
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        return new UserWorkingHoursRequest($request, $workingHours);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserWorkingHours $workingHour)
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        return $workingHour->load(['user', 'assignedBy']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserWorkingHours $workingHour)
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        return $this->fields($workingHour);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserWorkingHours $workingHour)
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        return new UserWorkingHoursRequest($request, $workingHour);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserWorkingHours $workingHour)
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        $workingHour->delete();

        return [
            'message' => 'Working hours assignment deleted successfully!',
        ];
    }

    /**
     * Get statistics for the dashboard.
     */
    public function statistics()
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        return [
            'total_custom' => UserWorkingHours::count(),
            'active' => UserWorkingHours::current()->count(),
            'expiring_soon' => UserWorkingHours::expiringSoon(7)->count(),
            'permanent' => UserWorkingHours::active()
                ->whereNull('effective_from')
                ->whereNull('effective_until')
                ->count(),
        ];
    }

    /**
     * Get users for dropdown.
     */
    public function users()
    {
        if (!$this->canManage()) {
            abort(403, 'Unauthorized action.');
        }

        return User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get current user's working hours (for dashboard/profile).
     */
    public function myWorkingHours()
    {
        $user = auth()->user();

        // Get current active working hours
        $currentHours = UserWorkingHours::forUser($user->id)
            ->current()
            ->orderBy('created_at', 'desc')
            ->first();

        // Get default working hours from options
        $defaultHours = option('default_working_hours', [
            'start' => '07:30',
            'end' => '16:30',
        ]);

        if (is_string($defaultHours)) {
            $defaultHours = json_decode($defaultHours, true);
        }

        if ($currentHours) {
            return [
                'has_custom_hours' => true,
                'start_time' => $currentHours->start_time,
                'end_time' => $currentHours->end_time,
                'formatted_start' => $currentHours->formatted_start_time,
                'formatted_end' => $currentHours->formatted_end_time,
                'effective_from' => $currentHours->effective_from,
                'effective_until' => $currentHours->effective_until,
                'is_permanent' => $currentHours->is_permanent,
                'effective_period' => $currentHours->effective_period,
                'reason' => $currentHours->reason,
                'assigned_at' => $currentHours->created_at,
            ];
        }

        return [
            'has_custom_hours' => false,
            'start_time' => $defaultHours['start'],
            'end_time' => $defaultHours['end'],
            'formatted_start' => Carbon::parse($defaultHours['start'])->format('g:i A'),
            'formatted_end' => Carbon::parse($defaultHours['end'])->format('g:i A'),
            'effective_from' => null,
            'effective_until' => null,
            'is_permanent' => true,
            'effective_period' => 'Default Schedule',
            'reason' => null,
            'assigned_at' => null,
        ];
    }

    /**
     * Get history of working hours for a user.
     */
    public function history(Request $request, $userId)
    {
        if (!$this->canManage() && auth()->id() != $userId) {
            abort(403, 'Unauthorized action.');
        }

        return UserWorkingHours::forUser($userId)
            ->with('assignedBy')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);
    }

    /**
     * Get form fields.
     */
    protected function fields(UserWorkingHours $workingHours)
    {
        $users = User::select('id', 'name as label', 'id as value')
            ->orderBy('name')
            ->get();

        return Field::make()
            ->field('user_id', $workingHours->user_id, $users)
            ->field('start_time', $workingHours->start_time ? Carbon::parse($workingHours->start_time)->format('H:i') : '07:30')
            ->field('end_time', $workingHours->end_time ? Carbon::parse($workingHours->end_time)->format('H:i') : '16:30')
            ->field('effective_from', $workingHours->effective_from?->format('Y-m-d'))
            ->field('effective_until', $workingHours->effective_until?->format('Y-m-d'))
            ->field('is_active', $workingHours->exists ? $workingHours->is_active : true)
            ->field('reason', $workingHours->reason);
    }
}
