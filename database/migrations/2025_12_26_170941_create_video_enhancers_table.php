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
        Schema::create('video_enhancers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('original_filename');
            $table->string('original_path');
            $table->bigInteger('original_size'); // in bytes
            $table->string('processed_path')->nullable();
            $table->bigInteger('processed_size')->nullable(); // in bytes
            $table->bigInteger('target_size')->nullable(); // user requested size in bytes
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('enhancement_options')->nullable(); // upscale, noise_reduction, compression_level, format
            $table->text('error_message')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_enhancers');
    }
};
