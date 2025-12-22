<?php

namespace App\Http\Requests;

use AhsanDev\Support\Requests\FormRequest;
use AhsanDev\Support\UpdateEnvConfig;
use Illuminate\Support\Facades\DB;

class SettingsGeneralRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'app_name' => 'required',
            'app_url' => 'required',
            'app_locale' => 'required',
            'app_timezone' => 'required',
            'app_direction' => 'required',
        ];
    }

    /**
     * Get the laravel app configs to change.
     *
     * @return array
     */
    public function configs()
    {
        return [
            'APP_NAME' => 'app_name',
            'APP_URL' => 'app_url',
            // 'APP_LOCALE' => 'app_locale',
            'APP_TIMEZONE' => 'app_timezone',
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
            new UpdateEnvConfig($this->request, $this->configs());

            option(
                $this->request->all()
            );
        });
    }
}
