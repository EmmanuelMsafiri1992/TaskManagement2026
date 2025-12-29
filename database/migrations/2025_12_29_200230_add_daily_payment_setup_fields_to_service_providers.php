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
            $table->foreignId('daily_starting_subject_id')->nullable()->after('daily_rate')->constrained('subjects')->nullOnDelete();
            $table->integer('daily_total_topics')->nullable()->after('daily_starting_subject_id');
            $table->boolean('daily_payment_setup_complete')->default(false)->after('daily_total_topics');
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
            $table->dropForeign(['daily_starting_subject_id']);
            $table->dropColumn(['daily_starting_subject_id', 'daily_total_topics', 'daily_payment_setup_complete']);
        });
    }
};
