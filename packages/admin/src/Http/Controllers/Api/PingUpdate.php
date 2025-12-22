<?php

namespace Admin\Http\Controllers\Api;

use Illuminate\Http\Request;

class PingUpdate
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if ($request->purchase_code == env('PURCHASE_CODE')) {
            option(['global_update_notification' => $request->version]);

            return ['success' => true];
        }

        return ['success' => false];
    }
}
