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
        Schema::create('user_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Daily targets
            $table->integer('daily_impressions_target')->default(2000);
            $table->integer('daily_page_views_target')->default(1500);
            $table->integer('daily_clicks_target')->default(20);
            $table->decimal('min_cpc_target', 8, 4)->default(0.10);
            $table->decimal('min_rpm_target', 8, 4)->default(1.00);
            $table->decimal('daily_earnings_target', 10, 2)->default(20.00);

            $table->timestamps();

            // Unique constraint - one target record per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_targets');
    }
};
