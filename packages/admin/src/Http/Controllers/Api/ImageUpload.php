<?php

namespace Admin\Http\Controllers\Api;

use Illuminate\Http\Request;

class ImageUpload
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if ($request->name) {
            return '/'.$request->file('image')->storeAs('img', $request->name.'.'.$request->file('image')->extension());
        }

        return '/'.$request->file('image')->store('img');
    }
}
