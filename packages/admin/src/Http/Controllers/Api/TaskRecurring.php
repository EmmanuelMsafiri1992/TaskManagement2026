<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Task;
use AhsanDev\Support\Recurring;
use Illuminate\Http\Request;

class TaskRecurring
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Task $task)
    {
        $recurring = Recurring::make($request->all());

        // Preserve existing meta data and add/update recurring pattern
        $meta = $task->meta ?? [];
        $meta['recurring'] = $recurring->pattern();

        $task->update([
            'recurring_at' => $recurring->nextIteration(),
            'meta' => $meta,
        ]);

        return $task;
    }
}
