<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TaskAssign
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
        $status = $task->users()->toggle(request('user'));

        if (count($status['attached'])) {
            Notification::send(User::find(request('user')), new TaskAssigned($task));
        }

        return ['success' => true];
    }
}
