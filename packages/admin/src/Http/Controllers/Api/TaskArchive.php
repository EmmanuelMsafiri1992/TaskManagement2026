<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;

class TaskArchive extends Controller
{
    public function __construct()
    {
        $this->middleware('can:task:delete');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Task $task)
    {
        $task->delete();

        return ['success' => true];
    }
}
