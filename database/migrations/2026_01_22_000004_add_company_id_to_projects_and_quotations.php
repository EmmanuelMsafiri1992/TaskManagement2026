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
        // Add company_id to projects
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('set null');
            }
        });

        // Add company_id to quotations
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('project_id')->constrained()->onDelete('set null');
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
            $table->dropConstrainedForeignId('company_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
