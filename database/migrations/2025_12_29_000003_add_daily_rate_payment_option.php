<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds daily rate payment option for service providers who want to be paid per day.
     * The system calculates required topics per day based on:
     * - Total agreed amount per subject (350,000 MWK)
     * - Daily rate chosen by teacher
     * - Total topics for assigned subjects
     */
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            // Daily rate chosen by the service provider (e.g., 40,000 MWK per day)
            $table->decimal('daily_rate', 12, 2)->nullable()->after('monthly_amount');

            // Amount per subject (default 350,000 MWK, half of 700,000 total)
            $table->decimal('amount_per_subject', 12, 2)->default(350000.00)->after('daily_rate');

            // Number of subjects assigned to this provider
            $table->integer('assigned_subjects_count')->default(2)->after('amount_per_subject');
        });

        // Update payment_preference enum to include 'daily' option
        // First, we need to modify the enum
        DB::statement("ALTER TABLE service_providers MODIFY COLUMN payment_preference ENUM('bi_weekly', 'monthly', 'lump_sum', 'daily') DEFAULT 'monthly'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back
        DB::statement("ALTER TABLE service_providers MODIFY COLUMN payment_preference ENUM('bi_weekly', 'monthly', 'lump_sum') DEFAULT 'monthly'");

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn(['daily_rate', 'amount_per_subject', 'assigned_subjects_count']);
        });
    }
};
