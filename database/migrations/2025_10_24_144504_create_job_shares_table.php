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
        Schema::create('job_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('v11_post_id')->comment('Post ID from V11 database');
            $table->string('post_title');
            $table->text('post_url');
            $table->string('country_code', 2);
            $table->string('shortened_url')->nullable();
            $table->text('formatted_content')->nullable()->comment('Pre-formatted content for social media');
            $table->boolean('copied')->default(false);
            $table->timestamp('copied_at')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('v11_post_id');
            $table->index('country_code');
            $table->index('copied');

            // Prevent duplicate assignments
            $table->unique(['user_id', 'v11_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_shares');
    }
};
