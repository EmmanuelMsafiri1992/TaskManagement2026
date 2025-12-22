<?php

namespace Admin\Http\Controllers\Api;

use App\Models\ChecklistItem;
use Illuminate\Http\Request;

class ChecklistItemComplete
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ChecklistItem  $item
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, ChecklistItem $item)
    {
        if ($item->completed_at) {
            $item->update(['completed_at' => null]);

            return ['success' => true, 'completed_at' => null];
        }

        $item->update([
            'completed_at' => now(),
        ]);

        return ['success' => true, 'completed_at' => 'true'];
    }
}
