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
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('objectives'); // Learning objectives
            $table->text('introduction')->nullable(); // Lesson introduction
            $table->text('main_content')->nullable(); // Main lesson content
            $table->text('activities')->nullable(); // Student activities
            $table->text('assessment')->nullable(); // How to assess understanding
            $table->text('conclusion')->nullable(); // Lesson conclusion/summary
            $table->text('homework')->nullable(); // Homework assignment
            $table->integer('duration_minutes')->nullable(); // Planned duration
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('feedback')->nullable(); // Admin feedback
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
        Schema::dropIfExists('lesson_plans');
    }
};
