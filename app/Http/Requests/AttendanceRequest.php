<?php

namespace App\Http\Requests;

use AhsanDev\Support\Requests\FormRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'date' => 'sometimes|date',
            'notes' => 'nullable|string',
        ];

        if ($this->request->action === 'manual') {
            $rules['user_id'] = 'required|exists:users,id';
            $rules['date'] = 'required|date';
            $rules['clock_in'] = 'required|date_format:Y-m-d H:i:s';
            $rules['clock_out'] = 'nullable|date_format:Y-m-d H:i:s|after:clock_in';
        }

        return $rules;
    }

    /**
     * Database Transaction.
     *
     * @return void
     */
    public function transaction()
    {
        return DB::transaction(function () {
            // Manual entry (admin creating/updating attendance)
            if ($this->request->action === 'manual') {
                $totalHours = null;
                if ($this->request->clock_in && $this->request->clock_out) {
                    $clockIn = Carbon::parse($this->request->clock_in);
                    $clockOut = Carbon::parse($this->request->clock_out);
                    $totalHours = $clockIn->diffInMinutes($clockOut);
                }

                return $this->model->updateOrCreate(
                    [
                        'user_id' => $this->request->user_id,
                        'date' => Carbon::parse($this->request->date)->format('Y-m-d'),
                    ],
                    [
                        'clock_in' => $this->request->clock_in,
                        'clock_out' => $this->request->clock_out,
                        'total_hours' => $totalHours,
                        'notes' => $this->request->notes,
                        'clock_in_ip' => $this->request->ip(),
                    ]
                );
            }

            // Clock In
            if ($this->request->action === 'clock_in') {
                $today = Carbon::today()->format('Y-m-d');
                $existing = $this->model
                    ->where('user_id', auth()->id())
                    ->where('date', $today)
                    ->first();

                if ($existing) {
                    return response()->json([
                        'message' => 'You have already clocked in today.',
                        'attendance' => $existing,
                    ], 422);
                }

                return $this->model->create([
                    'user_id' => auth()->id(),
                    'date' => $today,
                    'clock_in' => now(),
                    'clock_in_ip' => $this->request->ip(),
                    'notes' => $this->request->notes,
                ]);
            }

            // Clock Out
            if ($this->request->action === 'clock_out') {
                $today = Carbon::today()->format('Y-m-d');
                $attendance = $this->model
                    ->where('user_id', auth()->id())
                    ->where('date', $today)
                    ->whereNull('clock_out')
                    ->first();

                if (!$attendance) {
                    return response()->json([
                        'message' => 'No active clock-in found for today.',
                    ], 422);
                }

                $clockOut = now();
                $totalHours = $attendance->clock_in->diffInMinutes($clockOut);

                $attendance->update([
                    'clock_out' => $clockOut,
                    'clock_out_ip' => $this->request->ip(),
                    'total_hours' => $totalHours,
                    'notes' => $this->request->notes ?? $attendance->notes,
                ]);

                return $attendance;
            }

            // Update existing attendance
            if ($this->model->exists) {
                $data = [];

                if ($this->request->has('notes')) {
                    $data['notes'] = $this->request->notes;
                }

                if ($this->request->has('clock_out') && $this->model->clock_in) {
                    $clockOut = Carbon::parse($this->request->clock_out);
                    $data['clock_out'] = $clockOut;
                    $data['total_hours'] = $this->model->clock_in->diffInMinutes($clockOut);
                }

                if (!empty($data)) {
                    $this->model->update($data);
                }

                return $this->model;
            }
        });
    }
}
