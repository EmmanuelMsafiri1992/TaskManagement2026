<?php

namespace App\Http\Requests;

use App\Notifications\UserCreated;
use AhsanDev\Support\Requests\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserRequest extends FormRequest
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
            'email' => 'required',
            'password' => 'required',
            'role' => 'required',
        ];
    }

    /**
     * Get the update validation rules that apply to the request.
     *
     * @return array
     */
    public function updateRules()
    {
        return [
            'password' => 'nullable',
        ];
    }

    /**
     * Database Transaction.
     *
     * @return void
     */
    public function transaction()
    {
        if ($this->request->password) {
            $this->attributes['password'] = bcrypt($this->request->password);
        } else {
            unset($this->attributes['password']);
        }

        unset($this->attributes['role']);

        DB::transaction(function () {
            $this->model->forceFill($this->attributes);

            $this->model->save();
            $this->model->roles()->sync($this->request->role);
            Cache::forget('user:'.$this->model->id.':roles');

            if ($this->isPostRequest) {
                return $this->model->notify(new UserCreated($this->request->password));
            }
        });
    }
}
