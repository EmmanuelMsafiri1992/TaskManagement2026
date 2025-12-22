<?php

namespace Admin\Http\Controllers\Api;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Task;
use Illuminate\Http\Request;

class ChecklistItemsController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $checklist = Checklist::firstOrCreate(
            ['name' => 'Checklist', 'task_id' => $request->task_id],
        );

        $prevOrder = optional(ChecklistItem::whereChecklistId($checklist->id)->latest('id')->first())->order;
        $prevOrder = $prevOrder === null ? -1 : $prevOrder;

        return ChecklistItem::create([
            'title' => $request->title,
            'checklist_id' => $checklist->id,
            'order' => $prevOrder + 1,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ChecklistItem  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ChecklistItem $item)
    {
        $item->update($request->only('title'));

        return $item;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChecklistItem  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChecklistItem $item)
    {
        $item->delete();

        return [
            'success' => true,
        ];
    }
}
