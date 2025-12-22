<?php

namespace App\Http\Requests;

use App\Notifications\UserInvitation;
use AhsanDev\Support\Requests\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class InvitationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|unique:invitations',
            'role' => 'required',
        ];
    }

    /**
     * Database Transaction.
     *
     * @return void
     */
    public function transaction()
    {
        unset($this->attributes['role']);

        DB::transaction(function () {
            $this->model->forceFill($this->attributes);
            $this->model->role_id = $this->request->role;
            $this->model->save();

            $url = URL::signedRoute('invitation', ['invitation' => $this->model]);
            $this->model->notify(new UserInvitation($url));
        });
    }
}
