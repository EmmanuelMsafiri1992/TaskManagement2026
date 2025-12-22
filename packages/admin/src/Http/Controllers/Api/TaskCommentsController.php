<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Attachment;
use App\Models\Comment;
use Illuminate\Http\Request;

class TaskCommentsController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $taskId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $taskId)
    {
        $comment = Comment::create([
            'text' => request('text'),
            'user_id' => auth()->id(),
            'task_id' => $taskId,
        ]);

        foreach (request('attachments') as $attachment) {
            Attachment::create([
                'task_id' => $taskId,
                'comment_id' => $comment->id,
                'path' => $attachment['path'],
                'name' => $attachment['name'],
            ]);
        }

        return $comment->load('user', 'attachments');

        return ['success' => true];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return [
            'success' => true,
            'message' => 'Comment Deleted Successfully!',
        ];
    }
}
