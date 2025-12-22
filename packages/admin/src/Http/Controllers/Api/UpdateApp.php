<?php

namespace Admin\Http\Controllers\Api;

use Illuminate\Http\Request;
use ZipArchive;

class UpdateApp
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $zip = new ZipArchive;
        $res = $zip->open(base_path('spack.zip'));

        if ($res === true) {
            $zip->extractTo(base_path());
            $zip->close();
        }

        return [
            'status' => 'done',
        ];
    }
}
