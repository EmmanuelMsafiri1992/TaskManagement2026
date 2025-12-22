<?php

namespace Admin\Http\Controllers\Api;

use Illuminate\Http\Request;

class Notifications
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $notifications = auth()->user()->notifications;

        return [
            'new' => $notifications->whereNull('read_at')->count(),
            'notifications' => $notifications->whereNull('read_at'),
        ];
    }
}
