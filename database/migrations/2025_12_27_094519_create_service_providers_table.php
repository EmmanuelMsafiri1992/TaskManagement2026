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
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('national_id')->nullable();
            $table->string('address')->nullable();
            $table->string('specialty')->nullable(); // e.g., Mathematics, Physics
            $table->string('qualification')->nullable(); // e.g., Bachelor's, Master's
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])->default('pending');
            $table->boolean('agreement_signed')->default(false);
            $table->timestamp('agreement_signed_at')->nullable();
            $table->string('avatar')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->text('bio')->nullable();
            $table->json('meta')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('service_providers');
    }
};
