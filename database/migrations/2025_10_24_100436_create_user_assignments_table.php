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
        Schema::create('user_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taskhub_user_id')->constrained('users')->onDelete('cascade');

            // V11 database user details
            $table->unsignedBigInteger('v11_user_id');
            $table->tinyInteger('v11_user_type'); // 1=Employer, 2=Job Seeker
            $table->string('v11_user_name');
            $table->string('v11_user_email');

            // Assignment metadata
            $table->boolean('auto_assigned')->default(false);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at');
            $table->timestamp('last_contacted_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('taskhub_user_id');
            $table->index('v11_user_id');
            $table->index('v11_user_type');

            // Unique constraint - one v11 user can only be assigned to one TaskHub user
            $table->unique(['v11_user_id', 'v11_user_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_assignments');
    }
};
