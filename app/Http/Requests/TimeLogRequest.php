<?php

namespace App\Http\Requests;

use App\Models\Task;
use AhsanDev\Support\Requests\FormRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimeLogRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'taskId' => 'required',
        ];
    }

    /**
     * Database Transaction.
     *
     * @return void
     */
    public function transaction()
    {
        DB::transaction(function () {
            if ($this->request->manual) {
                $start = Carbon::parse($this->request->date);
                $stop = $start->copy()->addHours($this->request->hours)->addMinutes($this->request->minutes);
                $interval = $start->diffAsCarbonInterval($stop);

                return $this->model->create([
                    'user_id' => auth()->id(),
                    'task_id' => $this->request->taskId,
                    'project_id' => $this->request->projectId,
                    'is_manual' => true,
                    'started_at' => $start,
                    'stopped_at' => $stop,
                    'time' => $interval->format('%H:%I:%S'),
                    'time_human' => $interval,
                    'time_decimal' => $start->floatDiffInSeconds($stop),
                    'description' => $this->request->description,
                ]);
            }

            if ($this->request->start) {
                return $this->model->create([
                    'user_id' => auth()->id(),
                    'project_id' => $this->request->projectId,
                    'task_id' => $this->request->taskId,
                    'started_at' => Carbon::parse($this->request->currentTime),
                ]);
            } elseif ($this->request->stop) {
                $time = $this->model
                            ->whereUserId(auth()->id())
                            ->whereProjectId($this->request->projectId)
                            ->whereTaskId($this->request->taskId)
                            ->whereNull('stopped_at')
                            ->first();
                $start = $time->started_at;
                $stop = Carbon::parse($this->request->currentTime);
                $interval = $start->diffAsCarbonInterval($stop);

                $time->update([
                    'stopped_at' => $stop,
                    'time' => $interval->format('%H:%I:%S'),
                    'time_human' => $interval,
                    'time_decimal' => $start->floatDiffInSeconds($stop),
                    'time_seconds' => $start->diffInSeconds($stop),
                ]);

                Task::find($this->request->taskId)->update([
                    'total_seconds' => $this->model->whereTaskId($this->request->taskId)->sum('time_seconds'),
                ]);

                return $time;
            }
        });
    }
}
