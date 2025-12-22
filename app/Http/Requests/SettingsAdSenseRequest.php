<?php

namespace App\Http\Requests;

use AhsanDev\Support\Requests\FormRequest;
use AhsanDev\Support\UpdateEnvConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingsAdSenseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'adsense_client_id' => 'required|string',
            'adsense_client_secret' => 'required|string',
            'adsense_account_id' => 'nullable|string',
            'ga4_property_id' => 'nullable|string',
        ];
    }

    /**
     * Get the laravel app configs to change.
     *
     * @return array
     */
    public function configs()
    {
        return [];
    }

    /**
     * Database Transaction.
     *
     * @return void
     */
    public function transaction()
    {
        DB::transaction(function () {
            // Save OAuth credentials and account ID to database options
            $options = [
                'adsense_client_id' => $this->request->adsense_client_id,
                'adsense_client_secret' => $this->request->adsense_client_secret,
            ];

            if ($this->request->has('adsense_account_id')) {
                $options['adsense_account_id'] = $this->request->adsense_account_id;
            }

            if ($this->request->has('ga4_property_id')) {
                $options['ga4_property_id'] = $this->request->ga4_property_id;
            }

            option($options);

            Log::info('AdSense settings saved', [
                'has_client_id' => !empty($this->request->adsense_client_id),
                'has_client_secret' => !empty($this->request->adsense_client_secret),
                'has_account_id' => !empty($this->request->adsense_account_id),
                'has_ga4_property_id' => !empty($this->request->ga4_property_id),
                'user_id' => auth()->id(),
            ]);
        });
    }
}
