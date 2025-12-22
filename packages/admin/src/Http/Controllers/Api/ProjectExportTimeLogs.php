<?php

namespace Admin\Http\Controllers\Api;

use App\Models\TimeLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class ProjectExportTimeLogs
{
    /**
     * Handle the incoming request.
     *
     * @param  int  $projectId
     * @return \Illuminate\Http\Response
     */
    public function __invoke($projectId)
    {
        $csvFilename = 'timelogs_'.$projectId.'.csv';

        // these are the headers for the csv file.
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Disposition' => 'attachment; filename='.$csvFilename,
            'Expires' => '0',
            'Pragma' => 'public',
        ];

        // I am storing the csv file in public >> files folder. So that why I am creating files folder
        if (! File::exists(public_path().'/files')) {
            File::makeDirectory(public_path().'/files');
        }

        $logs = TimeLog::whereProjectId($projectId)->with('user', 'task')->get();

        // creating the download file
        $filename = public_path('files/'.$csvFilename);
        $handle = fopen($filename, 'w');

        // adding the first row
        fputcsv($handle, [
            'User',
            'Task',
            'Started_at',
            'Stopped_at',
            'Time',
            'Time Decimal',
            'Manual',
            'Description',
        ]);

        // adding the data from the array
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->user->name,
                $log->task->title,
                $log->started_at,
                $log->stopped_at,
                $log->time,
                $log->time_decimal,
                $log->is_manual,
                $log->description,
            ]);
        }

        fclose($handle);

        // download command
        return Response::download($filename, $csvFilename, $headers);
    }
}
