<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskMove
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
        $task->update([
            'project_list_id' => $request->toList,
            'order' => $request->newIndex,
        ]);

        // Old list
        $oldListTasks = Task::whereProjectId($request->projectId)
                            ->whereNull('parent_id')
                            ->where('project_list_id', $request->fromList)
                            ->where('order', '>', $request->oldIndex)
                            ->orderBy('order')
                            ->get();

        foreach ($oldListTasks as $oldListTask) {
            $oldListTask->update([
                'order' => $oldListTask->order - 1,
            ]);
        }

        // New list
        $newListTasks = Task::whereProjectId($request->projectId)
                            ->whereNull('parent_id')
                            ->where('project_list_id', $request->toList)
                            ->where('order', '>=', $request->newIndex)
                            ->where('id', '!=', $task->id)
                            ->orderBy('order')
                            ->get();

        foreach ($newListTasks as $newListTask) {
            $newListTask->update([
                'order' => $newListTask->order + 1,
            ]);
        }

        return ['status' => 'done'];
    }
}
