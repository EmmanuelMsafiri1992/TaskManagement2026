<?php

namespace Admin\Http\Controllers\Api;

use App\Models\TimeLog;

class ProjectTotalTime
{
    /**
     * Handle the incoming request.
     *
     * @param  int  $projectId
     * @return \Illuminate\Http\Response
     */
    public function __invoke($projectId)
    {
        return TimeLog::whereProjectId($projectId)->sum('time_decimal');
    }
}
