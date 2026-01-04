<?php

namespace App\Http\Requests;

use AhsanDev\Support\Requests\FormRequest;
use App\Models\UserWorkingHours;
use App\Notifications\WorkingHoursAssigned;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

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
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->hasOverlappingWorkingHours()) {
                $validator->errors()->add('user_id', 'This user already has active working hours for the specified period.');
            }
        });
    }

    /**
     * Check if there are overlapping active working hours for this user.
     */
    protected function hasOverlappingWorkingHours(): bool
    {
        $userId = $this->request->user_id;
        $effectiveFrom = $this->request->effective_from;
        $effectiveUntil = $this->request->effective_until;
        $isActive = $this->request->is_active ?? true;

        // Only check for overlaps if creating active working hours
        if (!$isActive) {
            return false;
        }

        $query = UserWorkingHours::where('user_id', $userId)
            ->where('is_active', true);

        // Exclude current record if updating
        if ($this->model->exists) {
            $query->where('id', '!=', $this->model->id);
        }

        // Check for overlapping periods
        $query->where(function ($q) use ($effectiveFrom, $effectiveUntil) {
            // Case 1: New record is permanent (no dates)
            if (is_null($effectiveFrom) && is_null($effectiveUntil)) {
                // Any active record overlaps with a permanent one
                $q->whereRaw('1 = 1');
            }
            // Case 2: Existing permanent records overlap with any new record
            else {
                $q->where(function ($permanent) {
                    $permanent->whereNull('effective_from')
                              ->whereNull('effective_until');
                })
                // Case 3: Check date range overlaps
                ->orWhere(function ($ranged) use ($effectiveFrom, $effectiveUntil) {
                    // Overlap exists if: start1 <= end2 AND end1 >= start2
                    $ranged->where(function ($overlap) use ($effectiveFrom, $effectiveUntil) {
                        if ($effectiveFrom) {
                            $overlap->where(function ($q) use ($effectiveFrom) {
                                $q->whereNull('effective_until')
                                  ->orWhere('effective_until', '>=', $effectiveFrom);
                            });
                        }
                        if ($effectiveUntil) {
                            $overlap->where(function ($q) use ($effectiveUntil) {
                                $q->whereNull('effective_from')
                                  ->orWhere('effective_from', '<=', $effectiveUntil);
                            });
                        }
                    });
                });
            }
        });

        return $query->exists();
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
