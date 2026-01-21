<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the disabled_sidebar_features option with empty array as default
        DB::table('options')->insert([
            'key' => 'disabled_sidebar_features',
            'value' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('options')->where('key', 'disabled_sidebar_features')->delete();
    }
};
