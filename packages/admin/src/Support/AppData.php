<?php

namespace Admin\Support;

use App\Models\FavoriteProject;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use JsonSerializable;
use Laranext\Span\Span;

class AppData implements JsonSerializable
{
    protected $config = [];

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->handle();
    }

    /**
     * Prepare configs.
     *
     * @return void
     */
    protected function handle()
    {
        $user = auth()->user();

        if (!$user) {
            // If no user is authenticated, return minimal config
            $this->config = [
                'csrf_token' => csrf_token(),
                'user' => null,
                'permissions' => [],
                'translations' => json_decode(file_get_contents(base_path('lang/'.option('app_locale', 'en').'.json')), true),
                'app_name' => env('APP_NAME', 'Spack'),
                'locale' => option('app_locale', 'en'),
            ];
            return;
        }

        $contractDaysRemaining = null;
        $employmentType = null;

        // Get employment type from employee record
        $employeeRecord = $user->employeeRecord;
        if ($employeeRecord) {
            $employmentType = $employeeRecord->employment_type;

            // Only calculate contract days remaining for Contract employees
            if ($employmentType === 'Contract' && $employeeRecord->contract_end_date) {
                $contractDaysRemaining = now()->diffInDays($employeeRecord->contract_end_date, false);
                $contractDaysRemaining = $contractDaysRemaining > 0 ? (int)$contractDaysRemaining : 0;
            }
        }

        $this->config = [
            'csrf_token' => csrf_token(),
            'prefix' => Span::prefix(),
            'user' => $user->only(['id', 'name', 'email', 'avatar', 'contract_end_date', 'contract_type']),
            'contract_days_remaining' => $contractDaysRemaining,
            'employment_type' => $employmentType,
            'options' => [
                'email_config' => option('email_config'),
                'global_update_notification' => option('global_update_notification'),
                'demo' => $this->isStandaloneDemo() && env('APP_DEMO', false),
                'is_standalone_demo' => $this->isStandaloneDemo(),
            ],
            'permissions' => $user->allPermissions(),
            // 'favorites' => FavoriteProject::whereUserId(auth()->id())
            //                     ->with('project')
            //                     ->get(),
            // 'projects' => Project::get(),
            'translations' => json_decode(file_get_contents(base_path('lang/'.option('app_locale', 'en').'.json')), true),
            'app_name' => env('APP_NAME', 'Spack'),
            'app_logo' => option('app_logo'),
            'is_super_admin' => $user->isSuperAdmin(),
            'is_attendance_admin' => $user->email === 'emmanuel@emphxs.com' || $user->isSuperAdmin(),
            'is_impersonating' => Session::has('impersonator_id'),
            'impersonator_id' => Session::get('impersonator_id'),
            'locale' => option('app_locale'),
            'PUSHER_APP_KEY' => config('broadcasting.connections.pusher.key'),
            'PUSHER_APP_CLUSTER' => config('broadcasting.connections.pusher.options.cluster'),
            'disabled_sidebar_features' => option('disabled_sidebar_features', []),
        ];
    }

    protected function isStandaloneDemo()
    {
        if (request()->getHttpHost() == 'localhost') {
            return false;
        }

        $host = explode('.', request()->getHttpHost());

        if (isset($host[1]) && $host[1] == 'spackdemo') {
            return true;
        }

        return false;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->config;
    }
}
