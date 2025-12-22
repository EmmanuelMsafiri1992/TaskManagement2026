<?php

namespace Admin\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;

class CacheCleanup
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        Cache::flush();

        return 'Done!';
    }
}
