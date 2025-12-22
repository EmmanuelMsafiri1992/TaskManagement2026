<?php

namespace Admin\Http\Controllers\Api;

use Illuminate\Http\Request;

class AvatarUpload
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $avatar = $request->file('avatar')->storeAs('img', 'avatar_'.auth()->id().'.'.$request->file('avatar')->extension());

        auth()->user()->update([
            'avatar' => $avatar,
        ]);

        return 'done';
    }
}
