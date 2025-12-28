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
        // Add payment fields to service_providers table
        Schema::table('service_providers', function (Blueprint $table) {
            $table->decimal('total_agreed_amount', 12, 2)->default(700000.00)->after('hourly_rate');
            $table->enum('payment_preference', ['monthly', 'lump_sum'])->default('monthly')->after('total_agreed_amount');
            $table->decimal('monthly_amount', 12, 2)->nullable()->after('payment_preference');
            $table->enum('payment_method', ['bank', 'mobile_money'])->nullable()->after('monthly_amount');
            $table->string('bank_name')->nullable()->after('payment_method');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            $table->string('bank_branch')->nullable()->after('bank_account_name');
            $table->string('mobile_money_provider')->nullable()->after('bank_branch'); // e.g., Airtel Money, TNM Mpamba
            $table->string('mobile_money_number')->nullable()->after('mobile_money_provider');
            $table->string('mobile_money_name')->nullable()->after('mobile_money_number');
            $table->decimal('total_paid', 12, 2)->default(0.00)->after('mobile_money_name');
        });

        // Create payments table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->onDelete('cascade');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['bank', 'mobile_money', 'cash', 'cheque'])->default('bank');
            $table->string('reference_number')->nullable();
            $table->string('transaction_id')->nullable();
            $table->date('payment_date');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('month_for')->nullable(); // e.g., "January 2026"
            $table->text('notes')->nullable();
            $table->string('receipt_number')->unique()->nullable();
            $table->timestamps();
        });

        // Update service_provider_agreements table to track PDF path
        Schema::table('service_provider_agreements', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('signed_at');
            $table->text('terms_content')->nullable()->after('pdf_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn([
                'total_agreed_amount',
                'payment_preference',
                'monthly_amount',
                'payment_method',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'bank_branch',
                'mobile_money_provider',
                'mobile_money_number',
                'mobile_money_name',
                'total_paid',
            ]);
        });

        Schema::dropIfExists('payments');

        Schema::table('service_provider_agreements', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'terms_content']);
        });
    }
};
