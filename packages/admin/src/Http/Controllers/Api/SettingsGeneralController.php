<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsGeneralRequest;
use AhsanDev\Support\Field;
use AhsanDev\Support\Languages;
use AhsanDev\Support\Timezonelist;
use Illuminate\Http\Request;

class SettingsGeneralController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:setting:general');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Field::make()
                ->field('app_logo', option('app_logo'))
                ->field('app_name', option('app_name'))
                ->field('app_url', option('app_url'))
                ->field('app_locale', option('app_locale'), $this->locales())
                ->field('app_direction', option('app_direction', 'ltr'), [['label' => 'LTR', 'value' => 'ltr'], ['label' => 'RTL', 'value' => 'rtl']])
                ->field('app_timezone', option('app_timezone'), $this->timezones());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return new SettingsGeneralRequest($request);
    }

    protected function locales()
    {
        $locales = [];

        foreach ((new Languages)->get() as $key => $language) {
            $locales[] = [
                'label' => $language,
                'value' => $key,
            ];
        }

        return $locales;
    }

    protected function timezones()
    {
        $timezone = new Timezonelist;

        return $timezone->toArray(false);
    }
}
