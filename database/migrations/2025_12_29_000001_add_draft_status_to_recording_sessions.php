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
        // Modify the status enum to include 'draft'
        DB::statement("ALTER TABLE recording_sessions MODIFY COLUMN status ENUM('draft', 'scheduled', 'in_progress', 'completed', 'cancelled', 'pending_review', 'approved', 'rejected') DEFAULT 'scheduled'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to original enum without 'draft'
        DB::statement("ALTER TABLE recording_sessions MODIFY COLUMN status ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'pending_review', 'approved', 'rejected') DEFAULT 'scheduled'");
    }
};
