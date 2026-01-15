<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add bi_weekly to payment_preference enum
        DB::statement("ALTER TABLE service_providers MODIFY COLUMN payment_preference ENUM('bi_weekly', 'monthly', 'lump_sum') DEFAULT 'monthly'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to original enum without bi_weekly
        DB::statement("ALTER TABLE service_providers MODIFY COLUMN payment_preference ENUM('monthly', 'lump_sum') DEFAULT 'monthly'");
    }
};
