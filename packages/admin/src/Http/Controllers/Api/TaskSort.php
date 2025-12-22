<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskSort
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
        if ($request->oldIndex < $request->newIndex) {
            $startDirection = '>=';
            $endDirection = '<=';
            $sign = -1;
        } elseif ($request->oldIndex > $request->newIndex) {
            $startDirection = '<=';
            $endDirection = '>=';
            $sign = 1;
        }

        Task::whereId($request->id)->update([
            'order' => $request->newIndex,
        ]);

        $tasks = Task::whereProjectId($request->projectId)
                            ->whereNull('parent_id')
                            ->where('project_list_id', $task->project_list_id)
                            ->where('order', $startDirection, $request->oldIndex)
                            ->where('order', $endDirection, $request->newIndex)
                            ->where('id', '!=', $request->id)
                            ->orderBy('order')
                            ->get();

        foreach ($tasks as $task) {
            $task->update([
                'order' => $task->order + $sign,
            ]);
        }

        return ['status' => 'done'];
    }
}
