<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('advance_requests')) {
            Schema::create('advance_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10)->default('MWK');
                $table->text('reason');
                $table->enum('status', ['pending', 'approved', 'rejected', 'deducted'])->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->text('admin_notes')->nullable();
                $table->decimal('amount_deducted', 15, 2)->default(0);
                $table->decimal('remaining_balance', 15, 2)->default(0);
                $table->foreignId('payroll_id')->nullable()->constrained()->onDelete('set null');
                $table->date('expected_deduction_date')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_requests');
    }
};
