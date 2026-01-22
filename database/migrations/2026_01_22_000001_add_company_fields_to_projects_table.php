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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'company_name')) {
                $table->string('company_name')->nullable()->after('description');
            }
            if (!Schema::hasColumn('projects', 'company_email')) {
                $table->string('company_email')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('projects', 'company_phone')) {
                $table->string('company_phone')->nullable()->after('company_email');
            }
            if (!Schema::hasColumn('projects', 'company_address')) {
                $table->text('company_address')->nullable()->after('company_phone');
            }
            if (!Schema::hasColumn('projects', 'company_logo')) {
                $table->string('company_logo')->nullable()->after('company_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'company_email', 'company_phone', 'company_address', 'company_logo']);
        });
    }
};
