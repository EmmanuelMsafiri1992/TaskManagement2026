<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (!Schema::hasTable('generator_fuel')) {
            Schema::create('generator_fuel', function (Blueprint $table) {
                $table->id();
                $table->decimal('current_level', 8, 2)->default(0);
                $table->decimal('tank_capacity', 8, 2)->default(12);
                $table->decimal('reserve_fuel', 8, 2)->default(2);
                $table->decimal('consumption_rate', 8, 2)->default(0.5);
                $table->boolean('is_running')->default(false);
                $table->timestamp('last_started_at')->nullable();
                $table->timestamp('last_stopped_at')->nullable();
                $table->timestamp('last_updated_at')->nullable();
                $table->timestamps();
            });

            // Insert default record
            DB::table('generator_fuel')->insert([
                'current_level' => 6,
                'tank_capacity' => 12,
                'reserve_fuel' => 2,
                'consumption_rate' => 0.5,
                'is_running' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generator_fuel');
    }
};
