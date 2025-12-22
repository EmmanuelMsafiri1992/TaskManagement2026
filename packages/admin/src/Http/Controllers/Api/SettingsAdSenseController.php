<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsAdSenseRequest;
use AhsanDev\Support\Field;
use Illuminate\Http\Request;

class SettingsAdSenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:setting:adsense');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $isConfigured = option('adsense_configured', false) && option('adsense_access_token');

        return Field::make()
                ->field('adsense_client_id', option('adsense_client_id'))
                ->field('adsense_client_secret', option('adsense_client_secret'))
                ->field('adsense_account_id', option('adsense_account_id'))
                ->field('ga4_property_id', option('ga4_property_id'))
                ->field('adsense_configured', $isConfigured);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return new SettingsAdSenseRequest($request);
    }
}
