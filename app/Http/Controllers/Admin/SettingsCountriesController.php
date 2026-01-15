<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCountry;
use App\Models\UserWebsite;
use App\Models\UserTarget;
use App\Models\V11\Country;
use App\Models\V11\Company;
use App\Notifications\CountryAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SettingsCountriesController extends Controller
{
    /**
     * Get all users with their assigned countries and websites
     */
    public function index(Request $request)
    {
        // Get all active countries from v11 database (with fallback if not available)
        try {
            $allCountries = Country::active()
                ->orderBy('code')
                ->get()
                ->map(function ($country) {
                    return [
                        'code' => $country->code,
                        'name' => $country->name,
                        'all_names' => $country->getAllNamesAttribute(),
                    ];
                })
                ->sortBy('name')
                ->values();
        } catch (\Exception $e) {
            // V11 database not available, use empty list
            $allCountries = collect([]);
        }

        $perPage = $request->input('per_page', 10);

        // Get users with their assigned countries (paginated)
        $usersPaginated = User::with(['roles', 'countries.websites', 'countries.assignedBy'])
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Marketer', 'Developer', 'Admin']);
            })
            ->orderBy('name')
            ->paginate($perPage);

        $users = $usersPaginated->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()->name ?? null,
                'countries' => $user->countries->map(function ($country) {
                    return [
                        'id' => $country->id,
                        'country_code' => $country->country_code,
                        'country_name' => $country->country_name,
                        'assigned_at' => $country->assigned_at ? $country->assigned_at->toISOString() : null,
                        'assigned_by' => optional($country->assignedBy)->name,
                        'websites' => $country->websites->map(function ($website) {
                            return [
                                'id' => $website->id,
                                'company_id' => $website->company_id,
                                'website_url' => $website->website_url,
                                'company_name' => $website->company_name,
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users,
                'countries' => $allCountries,
                'pagination' => [
                    'current_page' => $usersPaginated->currentPage(),
                    'last_page' => $usersPaginated->lastPage(),
                    'per_page' => $usersPaginated->perPage(),
                    'total' => $usersPaginated->total(),
                    'from' => $usersPaginated->firstItem(),
                    'to' => $usersPaginated->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Update user's assigned countries
     */
    public function updateUserCountries(Request $request, $userId)
    {
        $request->validate([
            'countries' => 'required|array',
            'countries.*.country_code' => 'required|string|size:2',
            'countries.*.country_name' => 'required|string',
        ]);

        $user = User::findOrFail($userId);
        $assignedBy = Auth::user();

        DB::transaction(function () use ($user, $request, $assignedBy) {
            // Get current country codes
            $currentCountryCodes = $user->countries->pluck('country_code')->toArray();
            $newCountryCodes = collect($request->countries)->pluck('country_code')->toArray();

            // Find newly assigned countries
            $newlyAssignedCodes = array_diff($newCountryCodes, $currentCountryCodes);

            // Delete countries that are no longer assigned
            $user->countries()->whereNotIn('country_code', $newCountryCodes)->delete();

            // Add or update countries
            foreach ($request->countries as $country) {
                $isNewAssignment = in_array($country['country_code'], $newlyAssignedCodes);

                $user->countries()->updateOrCreate(
                    ['country_code' => $country['country_code']],
                    [
                        'country_name' => $country['country_name'],
                        'assigned_at' => $isNewAssignment ? now() : null,
                        'assigned_by' => $isNewAssignment ? $assignedBy->id : null,
                    ]
                );
            }

            // Send notification for newly assigned countries
            if (!empty($newlyAssignedCodes)) {
                $newCountries = collect($request->countries)
                    ->whereIn('country_code', $newlyAssignedCodes)
                    ->toArray();

                $user->notify(new CountryAssigned($newCountries, $assignedBy));
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Countries updated successfully',
        ]);
    }

    /**
     * Get companies from v11 database by country
     */
    public function getCompaniesByCountry($countryCode)
    {
        try {
            $companies = Company::byCountry($countryCode)
                ->withWebsite()
                ->orderBy('name')
                ->get()
                ->map(function ($company) {
                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                        'website' => $company->website,
                        'email' => $company->email,
                        'phone' => $company->phone,
                    ];
                });
        } catch (\Exception $e) {
            $companies = collect([]);
        }

        return response()->json([
            'success' => true,
            'data' => $companies,
        ]);
    }

    /**
     * Assign websites/companies to user for a specific country
     */
    public function assignWebsitesToCountry(Request $request, $userId, $userCountryId)
    {
        $request->validate([
            'websites' => 'required|array',
            'websites.*.company_id' => 'required|integer',
            'websites.*.website_url' => 'required|url',
            'websites.*.company_name' => 'required|string',
        ]);

        $user = User::findOrFail($userId);
        $userCountry = UserCountry::where('id', $userCountryId)
            ->where('user_id', $userId)
            ->firstOrFail();

        DB::transaction(function () use ($userCountry, $request) {
            // Delete existing website assignments for this country
            $userCountry->websites()->delete();

            // Add new website assignments
            foreach ($request->websites as $website) {
                UserWebsite::create([
                    'user_id' => $userCountry->user_id,
                    'user_country_id' => $userCountry->id,
                    'company_id' => $website['company_id'],
                    'website_url' => $website['website_url'],
                    'company_name' => $website['company_name'],
                    'assigned_at' => now(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Websites assigned successfully',
        ]);
    }

    /**
     * Get or create user's performance targets
     */
    public function getUserTargets($userId)
    {
        $user = User::findOrFail($userId);

        // Get or create target with defaults
        $target = $user->target()->firstOrCreate(
            ['user_id' => $userId],
            [
                'daily_impressions_target' => 2000,
                'daily_page_views_target' => 1500,
                'daily_clicks_target' => 20,
                'min_cpc_target' => 0.10,
                'min_rpm_target' => 1.00,
                'daily_earnings_target' => 20.00,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $target,
        ]);
    }

    /**
     * Update user's performance targets
     */
    public function updateUserTargets(Request $request, $userId)
    {
        $request->validate([
            'daily_impressions_target' => 'required|integer|min:0',
            'daily_page_views_target' => 'required|integer|min:0',
            'daily_clicks_target' => 'required|integer|min:0',
            'min_cpc_target' => 'required|numeric|min:0',
            'min_rpm_target' => 'required|numeric|min:0',
            'daily_earnings_target' => 'required|numeric|min:0',
        ]);

        $user = User::findOrFail($userId);

        $target = $user->target()->updateOrCreate(
            ['user_id' => $userId],
            $request->only([
                'daily_impressions_target',
                'daily_page_views_target',
                'daily_clicks_target',
                'min_cpc_target',
                'min_rpm_target',
                'daily_earnings_target',
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Performance targets updated successfully',
            'data' => $target,
        ]);
    }

}
