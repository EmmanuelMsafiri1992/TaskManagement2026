<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Requests\TimeLogRequest;
use App\Models\TimeLog;
use Illuminate\Http\Request;

class TimeLogsController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Filters\TimeLogFilters  $filters
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = TimeLog::query();

        if (! auth()->user()->isSuperAdmin()) {
            $query->whereUserId(auth()->id());
        }

        return $query->whereNull('stopped_at')->with('user', 'task.project')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimeLog  $timeLog
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, TimeLog $timeLog)
    {
        return new TimeLogRequest($request, $timeLog);
    }
}
