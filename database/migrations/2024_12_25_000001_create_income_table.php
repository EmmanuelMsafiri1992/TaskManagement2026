<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('income')) {
            Schema::create('income', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->string('currency', 10)->default('MWK');
                $table->date('income_date');
                $table->enum('source', ['sales', 'services', 'consulting', 'adsense', 'quotation', 'other'])->default('other');
                $table->string('category')->nullable();
                $table->text('description');
                $table->string('invoice_number')->nullable();
                $table->string('client_name')->nullable();
                $table->foreignId('quotation_id')->nullable()->constrained('quotations')->onDelete('set null');
                $table->enum('status', ['pending', 'received', 'cancelled'])->default('pending');
                $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('received_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                // Indexes for better query performance
                $table->index(['status', 'income_date']);
                $table->index('source');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('income');
    }
};
