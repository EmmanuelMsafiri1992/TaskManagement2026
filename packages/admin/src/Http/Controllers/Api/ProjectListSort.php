<?php

namespace Admin\Http\Controllers\Api;

use App\Models\ProjectList;
use Illuminate\Http\Request;

class ProjectListSort
{
    /**
     * Handle the incoming request.
     *
     * @param  int  $projectId
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $projectId)
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

        ProjectList::whereId($request->id)->update([
            'order' => $request->newIndex,
        ]);

        $lists = ProjectList::whereProjectId($projectId)
                            ->where('order', $startDirection, $request->oldIndex)
                            ->where('order', $endDirection, $request->newIndex)
                            ->where('id', '!=', $request->id)
                            ->orderBy('order')
                            ->get();

        foreach ($lists as $list) {
            $list->update([
                'order' => $list->order + $sign,
            ]);
        }

        return ProjectList::whereProjectId($projectId)
            ->with(['tasks' => function ($q) {
                $q->orderBy('order');
            }, 'tasks.users', 'tasks.comments', 'tasks.tasks', 'tasks.checklists.checklistItems', 'tasks.priority', 'tasks.labels'])
            ->orderBy('order')
            ->get();
    }
}
