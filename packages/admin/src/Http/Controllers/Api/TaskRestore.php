<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Task;

class TaskRestore
{
    /**
     * Handle the incoming request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id)
    {
        Task::withTrashed()->whereId($id)->restore();

        return ['success' => true];
    }
}
