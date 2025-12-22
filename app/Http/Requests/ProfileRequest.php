<?php

namespace App\Http\Requests;

use AhsanDev\Support\Requests\FormRequest;
use Illuminate\Support\Facades\DB;

class ProfileRequest extends FormRequest
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
            if ($this->request->password) {
                $this->attributes['password'] = bcrypt($this->request->password);
            }

            $this->model->forceFill($this->attributes);

            $this->model->save();
        });
    }
}
