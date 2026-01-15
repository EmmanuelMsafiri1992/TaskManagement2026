<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('payroll_period');
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('basic_salary', 15, 2)->default(0);
                $table->decimal('allowances', 15, 2)->default(0);
                $table->decimal('bonuses', 15, 2)->default(0);
                $table->decimal('deductions', 15, 2)->default(0);
                $table->decimal('gross_salary', 15, 2)->default(0);
                $table->decimal('net_salary', 15, 2)->default(0);
                $table->string('currency', 10)->default('MWK');
                $table->enum('status', ['draft', 'approved', 'paid', 'sent'])->default('draft');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payroll_items')) {
            Schema::create('payroll_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
                $table->enum('item_type', ['allowance', 'bonus', 'deduction']);
                $table->string('description');
                $table->decimal('amount', 15, 2)->default(0);
                $table->string('category')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
    }
};
