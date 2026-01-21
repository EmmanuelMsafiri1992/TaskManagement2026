<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;

class SettingsSidebarFeaturesController extends Controller
{
    /**
     * Available sidebar features with their keys and labels
     */
    protected $sidebarFeatures = [
        ['key' => 'home', 'label' => 'Home', 'uri' => '/'],
        ['key' => 'tasks', 'label' => 'My Tasks', 'uri' => '/tasks'],
        ['key' => 'attendance', 'label' => 'Attendance', 'uri' => '/attendance'],
        ['key' => 'employees', 'label' => 'Employees', 'uri' => '/employees'],
        ['key' => 'working-hours', 'label' => 'Working Hours', 'uri' => '/working-hours'],
        ['key' => 'activity-reports', 'label' => 'Activity Reports', 'uri' => '/activity-reports'],
        ['key' => 'leaves', 'label' => 'Leave Requests', 'uri' => '/leaves'],
        ['key' => 'advance-requests', 'label' => 'Advance Requests', 'uri' => '/advance-requests'],
        ['key' => 'holidays', 'label' => 'Holidays', 'uri' => '/holidays'],
        ['key' => 'leads', 'label' => 'Leads', 'uri' => '/leads'],
        ['key' => 'financial', 'label' => 'Financial', 'uri' => null],
        ['key' => 'payrolls', 'label' => 'Payroll', 'uri' => '/payrolls'],
        ['key' => 'clients', 'label' => 'Clients', 'uri' => '/clients'],
        ['key' => 'quotations', 'label' => 'Quotations', 'uri' => '/quotations'],
        ['key' => 'expenses', 'label' => 'Expenses', 'uri' => '/expenses'],
        ['key' => 'income', 'label' => 'Income', 'uri' => '/income'],
        ['key' => 'profit-loss', 'label' => 'Profit & Loss', 'uri' => '/profit-loss'],
        ['key' => 'users', 'label' => 'Team Members', 'uri' => '/users'],
        ['key' => 'service-providers', 'label' => 'Service Providers', 'uri' => null],
        ['key' => 'adsense-reports', 'label' => 'AdSense Reports', 'uri' => '/adsense-reports'],
        ['key' => 'video-enhancer', 'label' => 'Video Enhancer', 'uri' => '/video-enhancer'],
        ['key' => 'audit-trail', 'label' => 'Audit Trail', 'uri' => '/audit-trail'],
    ];

    public function __construct()
    {
        $this->middleware('can:setting:general');
    }

    /**
     * Get all sidebar features with their enabled/disabled state
     */
    public function index()
    {
        $disabledFeatures = option('disabled_sidebar_features', []);

        $features = array_map(function ($feature) use ($disabledFeatures) {
            return [
                'key' => $feature['key'],
                'label' => $feature['label'],
                'uri' => $feature['uri'],
                'enabled' => !in_array($feature['key'], $disabledFeatures),
            ];
        }, $this->sidebarFeatures);

        return response()->json([
            'features' => $features,
        ]);
    }

    /**
     * Update the disabled sidebar features
     */
    public function store(Request $request)
    {
        $request->validate([
            'disabled_features' => 'array',
            'disabled_features.*' => 'string',
        ]);

        $option = new Option();
        $option->set('disabled_sidebar_features', $request->disabled_features ?? []);

        return response()->json([
            'message' => 'Sidebar features updated successfully!',
        ]);
    }
}
