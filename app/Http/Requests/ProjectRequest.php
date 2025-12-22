<?php

namespace App\Http\Requests;

use App\Notifications\ProjectAssigned;
use AhsanDev\Support\Requests\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ProjectRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:3|max:190',
            'description' => 'nullable',
            'color' => 'required',
            'users' => 'required|array',
        ];
    }

    /**
     * Database Transaction.
     *
     * @return void
     */
    public function transaction()
    {
        DB::transaction(function () {
            unset($this->attributes['users']);
            unset($this->attributes['color']);
            $this->attributes['meta'] = ['color' => $this->request->color];

            $this->model->forceFill($this->attributes);
            $this->model->save();

            $this->model->users()->sync($this->request->users);

            // Create default lists when creating a new project
            if ($this->isPostRequest) {
                $defaultLists = ['To Do', 'In Progress', 'Done'];
                foreach ($defaultLists as $index => $listName) {
                    $this->model->lists()->create([
                        'name' => $listName,
                        'order' => $index,
                    ]);
                }

                Notification::send($this->model->users, new ProjectAssigned($this->model));
            }
        });
    }
}
