<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use AhsanDev\Support\Field;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Attendance::query()->with('user');
        $user = auth()->user();
        $isAttendanceAdmin = $user->email === 'emmanuel@emphxs.com' || $user->isSuperAdmin();

        // Non-admin users can only see their own records
        if (!$isAttendanceAdmin) {
            $query->where('user_id', $user->id);
        } else {
            // Admin can filter by user
            if ($request->has('user_id') && $request->user_id != '') {
                $query->where('user_id', $request->user_id);
            }
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->has('date')) {
            $query->where('date', $request->date);
        } elseif ($request->has('month')) {
            $month = Carbon::parse($request->month);
            $query->whereYear('date', $month->year)
                  ->whereMonth('date', $month->month);
        }

        return $query->orderBy('date', 'desc')
                     ->orderBy('clock_in', 'desc')
                     ->paginate($request->per_page ?? 50);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function create(Attendance $attendance)
    {
        $user = auth()->user();
        $isAttendanceAdmin = $user->email === 'emmanuel@emphxs.com' || $user->isSuperAdmin();

        // Non-admin users cannot access create form
        if (!$isAttendanceAdmin) {
            abort(403, 'Unauthorized action.');
        }

        return $this->fields($attendance);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Attendance $attendance)
    {
        return new AttendanceRequest($request, $attendance);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function show(Attendance $attendance)
    {
        return $attendance->load('user');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function edit(Attendance $attendance)
    {
        $user = auth()->user();
        $isAttendanceAdmin = $user->email === 'emmanuel@emphxs.com' || $user->isSuperAdmin();

        // Non-admin users cannot edit records
        if (!$isAttendanceAdmin) {
            abort(403, 'Unauthorized action.');
        }

        return $this->fields($attendance);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attendance $attendance)
    {
        $user = auth()->user();
        $isAttendanceAdmin = $user->email === 'emmanuel@emphxs.com' || $user->isSuperAdmin();

        // Allow users to clock out their own attendance
        $isClockingOut = $request->action === 'clock_out' && $attendance->user_id === $user->id;

        // Non-admin users can only clock out their own records
        if (!$isAttendanceAdmin && !$isClockingOut) {
            abort(403, 'Unauthorized action.');
        }

        return new AttendanceRequest($request, $attendance);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attendance $attendance)
    {
        $user = auth()->user();
        $isAttendanceAdmin = $user->email === 'emmanuel@emphxs.com' || $user->isSuperAdmin();

        // Non-admin users cannot delete records
        if (!$isAttendanceAdmin) {
            abort(403, 'Unauthorized action.');
        }

        $attendance->delete();

        return [
            'message' => 'Attendance Record Deleted Successfully!',
        ];
    }

    /**
     * Get current user's attendance status for today.
     *
     * @return \Illuminate\Http\Response
     */
    public function status()
    {
        $today = Carbon::today()->format('Y-m-d');
        $attendance = Attendance::where('user_id', auth()->id())
                                ->where('date', $today)
                                ->first();

        return [
            'attendance' => $attendance,
            'is_clocked_in' => $attendance && !$attendance->clock_out,
        ];
    }

    /**
     * Get attendance report/summary.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        $query = Attendance::query()->with('user');

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $attendances = $query->get();

        // Calculate summary statistics
        $totalDays = $attendances->count();
        $totalHours = $attendances->sum('total_hours');
        $averageHours = $totalDays > 0 ? round($totalHours / $totalDays, 2) : 0;

        // Group by user for multi-user reports
        $byUser = $attendances->groupBy('user_id')->map(function ($userAttendances) {
            $user = $userAttendances->first()->user;
            return [
                'user' => $user,
                'total_days' => $userAttendances->count(),
                'total_hours' => $userAttendances->sum('total_hours'),
                'average_hours' => round($userAttendances->avg('total_hours'), 2),
            ];
        })->values();

        return [
            'summary' => [
                'total_days' => $totalDays,
                'total_hours' => $totalHours,
                'average_hours' => $averageHours,
            ],
            'by_user' => $byUser,
            'attendances' => $attendances,
        ];
    }

    /**
     * Get all users for attendance management.
     *
     * @return \Illuminate\Http\Response
     */
    public function users()
    {
        return User::select('id', 'name', 'email')
                   ->orderBy('name')
                   ->get();
    }

    /**
     * Get form fields.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return array
     */
    protected function fields(Attendance $attendance)
    {
        $users = User::select('id', 'name as label', 'id as value')
                    ->orderBy('name')
                    ->get();

        return Field::make()
                ->field('user_id', $attendance->user_id, $users)
                ->field('date', $attendance->date ? $attendance->date->format('Y-m-d') : null)
                ->field('clock_in', $attendance->clock_in ? $attendance->clock_in->format('Y-m-d\TH:i') : null)
                ->field('clock_out', $attendance->clock_out ? $attendance->clock_out->format('Y-m-d\TH:i') : null)
                ->field('notes', $attendance->notes);
    }
}
