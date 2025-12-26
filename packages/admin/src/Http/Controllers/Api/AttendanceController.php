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
     * Get attendance report/summary statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Present today - count of unique users who clocked in today
        $presentToday = Attendance::where('date', $today)->count();

        // Average hours worked today (total_hours is stored in minutes)
        $todayRecords = Attendance::where('date', $today)->whereNotNull('total_hours')->get();
        $avgHoursToday = $todayRecords->count() > 0
            ? round($todayRecords->avg('total_hours') / 60, 1)
            : 0;

        // Total attendance records this month
        $totalThisMonth = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])->count();

        // Average hours per record this month (total_hours is stored in minutes)
        $monthRecords = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('total_hours')
            ->get();
        $avgHoursMonth = $monthRecords->count() > 0
            ? round($monthRecords->avg('total_hours') / 60, 1)
            : 0;

        return [
            'present_today' => $presentToday,
            'avg_hours_today' => $avgHoursToday,
            'total_this_month' => $totalThisMonth,
            'avg_hours_month' => $avgHoursMonth,
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
     * Export attendance records to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = Attendance::query()->with('user');

        // Filter by user
        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->where('date', '<=', $request->end_date);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $filename = 'attendance_report_' . Carbon::now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, ['Employee', 'Date', 'Clock In', 'Clock Out', 'Total Hours', 'Notes']);

            // CSV Data
            foreach ($attendances as $record) {
                $totalHours = $record->total_hours
                    ? floor($record->total_hours / 60) . 'h ' . ($record->total_hours % 60) . 'm'
                    : '-';

                fputcsv($file, [
                    $record->user->name ?? 'N/A',
                    $record->date->format('Y-m-d'),
                    $record->clock_in ? $record->clock_in->format('H:i') : '-',
                    $record->clock_out ? $record->clock_out->format('H:i') : '-',
                    $totalHours,
                    $record->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
