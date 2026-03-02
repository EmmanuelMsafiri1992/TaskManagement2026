<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Assign only high-priority countries to Weston and Nathan.
     * - Weston: Top 20 earners from Prisca's original countries
     * - Nathan: Christina's 4 high-earners + 16 mid-tier countries
     * No overlaps between users.
     */
    public function up()
    {
        // Get user IDs by email
        $weston = DB::table('users')->where('email', 'weston@emphxs.com')->first();
        $nathan = DB::table('users')->where('email', 'nathan@emphxs.com')->first();

        if (!$weston || !$nathan) {
            return;
        }

        // Weston's 20 high-priority countries (from Prisca's original)
        $westonCountries = [
            'PH' => 'Philippines',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'KY' => 'Cayman Islands',
            'BS' => 'Bahamas',
            'AU' => 'Australia',
            'QA' => 'Qatar',
            'PL' => 'Poland',
            'DE' => 'Germany',
            'AT' => 'Austria',
            'HK' => 'Hong Kong',
            'NL' => 'Netherlands',
            'FR' => 'France',
            'CH' => 'Switzerland',
            'CZ' => 'Czechia',
            'JP' => 'Japan',
            'IT' => 'Italy',
        ];

        // Nathan's 20 countries (Christina's + mid-tier)
        $nathanCountries = [
            'IN' => 'India',
            'NG' => 'Nigeria',
            'ZA' => 'South Africa',
            'KE' => 'Kenya',
            'KW' => 'Kuwait',
            'GU' => 'Guam',
            'ES' => 'Spain',
            'BE' => 'Belgium',
            'NZ' => 'New Zealand',
            'HR' => 'Croatia',
            'MT' => 'Malta',
            'FI' => 'Finland',
            'RO' => 'Romania',
            'NO' => 'Norway',
            'SG' => 'Singapore',
            'DK' => 'Denmark',
            'OM' => 'Oman',
            'IS' => 'Iceland',
            'SE' => 'Sweden',
            'SK' => 'Slovakia',
        ];

        // Delete all current country assignments for both users
        DB::table('user_countries')->whereIn('user_id', [$weston->id, $nathan->id])->delete();

        // Assign Weston's countries
        foreach ($westonCountries as $code => $name) {
            DB::table('user_countries')->insert([
                'user_id' => $weston->id,
                'country_code' => $code,
                'country_name' => $name,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign Nathan's countries
        foreach ($nathanCountries as $code => $name) {
            DB::table('user_countries')->insert([
                'user_id' => $nathan->id,
                'country_code' => $code,
                'country_name' => $name,
                'assigned_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Cannot reliably reverse - would need to restore original assignments
    }
};
