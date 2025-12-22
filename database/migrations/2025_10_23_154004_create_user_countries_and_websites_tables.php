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
        // Create websites table
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('domain')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create user_countries pivot table
        Schema::create('user_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('country_code', 2);
            $table->string('country_name');
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['user_id', 'country_code']);
        });

        // Create user_websites pivot table
        Schema::create('user_websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_country_id')->constrained()->onDelete('cascade');
            $table->integer('company_id'); // ID from v11 database
            $table->string('website_url');
            $table->string('company_name');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['user_country_id', 'company_id']);
        });

        // Add domain column to adsense_reports if it doesn't exist
        if (!Schema::hasColumn('adsense_reports', 'domain')) {
            Schema::table('adsense_reports', function (Blueprint $table) {
                $table->string('domain')->nullable()->after('country_name');
                $table->dropUnique(['report_date', 'country_code']);
                $table->unique(['report_date', 'country_code', 'domain']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restore adsense_reports table
        if (Schema::hasColumn('adsense_reports', 'domain')) {
            Schema::table('adsense_reports', function (Blueprint $table) {
                $table->dropUnique(['report_date', 'country_code', 'domain']);
                $table->dropColumn('domain');
                $table->unique(['report_date', 'country_code']);
            });
        }

        Schema::dropIfExists('user_websites');
        Schema::dropIfExists('user_countries');
        Schema::dropIfExists('websites');
    }
};
