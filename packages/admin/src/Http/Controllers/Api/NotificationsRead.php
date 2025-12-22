<?php

namespace Admin\Http\Controllers\Api;

class NotificationsRead
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return [
            'success' => true,
        ];
    }
}
