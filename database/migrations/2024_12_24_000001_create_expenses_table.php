<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10)->default('MWK');
                $table->date('expense_date');
                $table->string('category')->nullable();
                $table->string('description');
                $table->text('notes')->nullable();
                $table->string('receipt_path')->nullable();
                $table->string('receipt_name')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
