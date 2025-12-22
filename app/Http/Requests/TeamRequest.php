<?php

namespace App\Http\Requests;

use AhsanDev\Support\Requests\FormRequest;
use Illuminate\Support\Facades\DB;

class TeamRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'users' => 'required',
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
            $this->attributes['key'] = $this->request->name;
            $this->model->forceFill($this->attributes);

            $this->model->save();
            $this->model->users()->sync($this->request->users);
        });
    }
}
