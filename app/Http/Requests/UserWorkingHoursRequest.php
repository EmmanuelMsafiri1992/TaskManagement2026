<?php

namespace App\Http\Requests;

use AhsanDev\Support\Requests\FormRequest;
use App\Models\UserWorkingHours;
use App\Notifications\WorkingHoursAssigned;
use Illuminate\Support\Facades\DB;

class UserWorkingHoursRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'boolean',
            'reason' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'end_time.after' => 'End time must be after start time.',
            'effective_until.after_or_equal' => 'End date must be on or after the start date.',
        ];
    }

    /**
     * Database Transaction.
     *
     * @return void
     */
    public function transaction()
    {
        return DB::transaction(function () {
            $isNew = !$this->model->exists;

            $this->model->fill([
                'user_id' => $this->request->user_id,
                'start_time' => $this->request->start_time,
                'end_time' => $this->request->end_time,
                'effective_from' => $this->request->effective_from,
                'effective_until' => $this->request->effective_until,
                'is_active' => $this->request->is_active ?? true,
                'reason' => $this->request->reason,
                'assigned_by' => auth()->id(),
            ]);

            $this->model->save();

            // Send notification to the user
            if ($isNew || $this->shouldNotifyUser()) {
                $user = $this->model->user;
                if ($user) {
                    $user->notify(new WorkingHoursAssigned($this->model));
                }
            }

            return $this->model->load('user', 'assignedBy');
        });
    }

    /**
     * Determine if user should be notified on update.
     */
    protected function shouldNotifyUser(): bool
    {
        // Notify if times changed or dates changed
        return $this->model->wasChanged(['start_time', 'end_time', 'effective_from', 'effective_until']);
    }
}
