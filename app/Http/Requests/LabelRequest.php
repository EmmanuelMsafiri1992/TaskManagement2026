<?php

namespace App\Http\Requests;

use AhsanDev\Support\Requests\FormRequest;
use Illuminate\Support\Facades\DB;

class LabelRequest extends FormRequest
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
            'color' => 'required',
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
            unset($this->attributes['color']);
            $this->attributes['meta'] = ['color' => $this->request->color];

            $this->model->forceFill($this->attributes);
            $this->model->save();
        });
    }
}
