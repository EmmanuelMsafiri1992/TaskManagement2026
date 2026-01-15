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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Quadratic Equations", "Organic Chemistry"
            $table->string('code')->nullable(); // Topic code
            $table->integer('term')->nullable(); // Term 1, 2, or 3
            $table->integer('week')->nullable(); // Week number
            $table->text('description')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->integer('estimated_hours')->nullable(); // Estimated teaching hours
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('topics');
    }
};
