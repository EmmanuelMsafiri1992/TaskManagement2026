<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            // Drop the foreign key and column
            $table->dropForeign(['daily_starting_subject_id']);
            $table->dropColumn('daily_starting_subject_id');

            // Add new column for subject name
            $table->string('daily_subject_name')->nullable()->after('daily_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn('daily_subject_name');

            // Restore the old column
            $table->foreignId('daily_starting_subject_id')->nullable()->after('daily_rate')->constrained('subjects')->nullOnDelete();
        });
    }
};
