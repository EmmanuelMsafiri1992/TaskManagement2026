<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Swap country assignments:
     * - Prisca (user_id: 6) countries -> Weston (user_id: 7)
     * - Christina (user_id: 5) countries -> Nathan (user_id: 4)
     *
     * @return void
     */
    public function up()
    {
        // Check if swap already happened (Prisca/Christina have no countries, Weston/Nathan do)
        $priscaCount = DB::table('user_countries')->where('user_id', 6)->count();
        $christinaCount = DB::table('user_countries')->where('user_id', 5)->count();

        // If already swapped locally, skip
        if ($priscaCount == 0 && $christinaCount == 0) {
            return;
        }

        // Delete current assignments for Weston (7) and Nathan (4)
        DB::table('user_countries')->whereIn('user_id', [4, 7])->delete();

        // Assign Prisca's countries (6) to Weston (7)
        DB::table('user_countries')
            ->where('user_id', 6)
            ->update([
                'user_id' => 7,
                'assigned_at' => now(),
            ]);

        // Assign Christina's countries (5) to Nathan (4)
        DB::table('user_countries')
            ->where('user_id', 5)
            ->update([
                'user_id' => 4,
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
        // Swap back: Weston -> Prisca, Nathan -> Christina
        DB::table('user_countries')
            ->where('user_id', 7)
            ->update([
                'user_id' => 6,
                'assigned_at' => now(),
            ]);

        DB::table('user_countries')
            ->where('user_id', 4)
            ->update([
                'user_id' => 5,
                'assigned_at' => now(),
            ]);
    }
};
