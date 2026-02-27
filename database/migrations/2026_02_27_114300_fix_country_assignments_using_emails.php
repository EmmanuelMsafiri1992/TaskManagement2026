<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Swap country assignments using email addresses (not hardcoded IDs):
     * - Prisca's countries -> Weston
     * - Christina's countries -> Nathan
     *
     * @return void
     */
    public function up()
    {
        // Get user IDs by email
        $prisca = DB::table('users')->where('email', 'prisca@emphxs.com')->first();
        $christina = DB::table('users')->where('email', 'christina@emphxs.com')->first();
        $weston = DB::table('users')->where('email', 'weston@emphxs.com')->first();
        $nathan = DB::table('users')->where('email', 'nathan@emphxs.com')->first();

        // If any user not found, skip
        if (!$prisca || !$christina || !$weston || !$nathan) {
            return;
        }

        // Check if Prisca/Christina still have countries (swap not done yet)
        $priscaCount = DB::table('user_countries')->where('user_id', $prisca->id)->count();
        $christinaCount = DB::table('user_countries')->where('user_id', $christina->id)->count();

        // If Prisca and Christina have no countries, swap already done
        if ($priscaCount == 0 && $christinaCount == 0) {
            return;
        }

        // Delete current assignments for Weston and Nathan
        DB::table('user_countries')->whereIn('user_id', [$weston->id, $nathan->id])->delete();

        // Assign Prisca's countries to Weston
        DB::table('user_countries')
            ->where('user_id', $prisca->id)
            ->update([
                'user_id' => $weston->id,
                'assigned_at' => now(),
            ]);

        // Assign Christina's countries to Nathan
        DB::table('user_countries')
            ->where('user_id', $christina->id)
            ->update([
                'user_id' => $nathan->id,
                'assigned_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Get user IDs by email
        $prisca = DB::table('users')->where('email', 'prisca@emphxs.com')->first();
        $christina = DB::table('users')->where('email', 'christina@emphxs.com')->first();
        $weston = DB::table('users')->where('email', 'weston@emphxs.com')->first();
        $nathan = DB::table('users')->where('email', 'nathan@emphxs.com')->first();

        if (!$prisca || !$christina || !$weston || !$nathan) {
            return;
        }

        // Swap back: Weston -> Prisca, Nathan -> Christina
        DB::table('user_countries')
            ->where('user_id', $weston->id)
            ->update([
                'user_id' => $prisca->id,
                'assigned_at' => now(),
            ]);

        DB::table('user_countries')
            ->where('user_id', $nathan->id)
            ->update([
                'user_id' => $christina->id,
                'assigned_at' => now(),
            ]);
    }
};
