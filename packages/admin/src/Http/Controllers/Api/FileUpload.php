<?php

namespace Admin\Http\Controllers\Api;

use Illuminate\Http\Request;

class FileUpload
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return $request->file('file')->store('files');
    }
}
