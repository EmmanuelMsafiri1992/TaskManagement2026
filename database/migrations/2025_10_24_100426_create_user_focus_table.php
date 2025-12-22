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
        Schema::create('user_focus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Focus areas
            $table->boolean('works_with_job_seekers')->default(false);
            $table->boolean('works_with_employers')->default(false);

            // Auto-assignment settings
            $table->boolean('enable_auto_assign_job_seekers')->default(false);
            $table->boolean('enable_auto_assign_employers')->default(false);

            $table->timestamps();

            // Unique constraint - one focus record per user
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
        Schema::dropIfExists('user_focus');
    }
};
