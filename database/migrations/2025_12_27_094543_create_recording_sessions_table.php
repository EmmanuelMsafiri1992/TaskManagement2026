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
        Schema::create('recording_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('lesson_plan_id')->nullable();
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->timestamp('recording_start')->nullable();
            $table->timestamp('recording_end')->nullable();
            $table->integer('total_minutes')->nullable(); // Total session time
            $table->integer('recording_minutes')->nullable(); // Actual recording time
            $table->string('video_file')->nullable(); // Video file path/reference
            $table->string('video_duration')->nullable(); // Video duration
            $table->integer('retakes')->default(0); // Number of retakes
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'pending_review', 'approved', 'rejected'])->default('scheduled');
            $table->integer('quality_rating')->nullable(); // 1-5 rating
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recording_sessions');
    }
};
