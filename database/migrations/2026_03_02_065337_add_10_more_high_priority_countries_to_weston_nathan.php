<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 10 more high-priority countries to each user.
     * Based on AdSense earnings report.
     * After this, each user will have 30 countries total.
     */
    public function up()
    {
        // Get user IDs by email
        $weston = DB::table('users')->where('email', 'weston@emphxs.com')->first();
        $nathan = DB::table('users')->where('email', 'nathan@emphxs.com')->first();

        if (!$weston || !$nathan) {
            return;
        }

        // Weston's 10 additional high-priority countries
        $westonNewCountries = [
            'IE' => 'Ireland',
            'GM' => 'Gambia',
            'HU' => 'Hungary',
            'PT' => 'Portugal',
            'TR' => 'Turkey',
            'BH' => 'Bahrain',
            'JM' => 'Jamaica',
            'TW' => 'Taiwan',
            'ZM' => 'Zambia',
            'MY' => 'Malaysia',
        ];

        // Nathan's 10 additional high-priority countries
        $nathanNewCountries = [
            'TT' => 'Trinidad and Tobago',
            'MW' => 'Malawi',
            'PK' => 'Pakistan',
            'BR' => 'Brazil',
            'GR' => 'Greece',
            'GD' => 'Grenada',
            'AS' => 'American Samoa',
            'AG' => 'Antigua and Barbuda',
            'GH' => 'Ghana',
            'KH' => 'Cambodia',
        ];

        // Add Weston's new countries
        foreach ($westonNewCountries as $code => $name) {
            // Check if already exists to avoid duplicates
            $exists = DB::table('user_countries')
                ->where('user_id', $weston->id)
                ->where('country_code', $code)
                ->exists();

            if (!$exists) {
                DB::table('user_countries')->insert([
                    'user_id' => $weston->id,
                    'country_code' => $code,
                    'country_name' => $name,
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Add Nathan's new countries
        foreach ($nathanNewCountries as $code => $name) {
            // Check if already exists to avoid duplicates
            $exists = DB::table('user_countries')
                ->where('user_id', $nathan->id)
                ->where('country_code', $code)
                ->exists();

            if (!$exists) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $weston = DB::table('users')->where('email', 'weston@emphxs.com')->first();
        $nathan = DB::table('users')->where('email', 'nathan@emphxs.com')->first();

        if ($weston) {
            DB::table('user_countries')
                ->where('user_id', $weston->id)
                ->whereIn('country_code', ['IE', 'GM', 'HU', 'PT', 'TR', 'BH', 'JM', 'TW', 'ZM', 'MY'])
                ->delete();
        }

        if ($nathan) {
            DB::table('user_countries')
                ->where('user_id', $nathan->id)
                ->whereIn('country_code', ['TT', 'MW', 'PK', 'BR', 'GR', 'GD', 'AS', 'AG', 'GH', 'KH'])
                ->delete();
        }
    }
};
