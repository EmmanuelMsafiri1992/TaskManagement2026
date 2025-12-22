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
        Schema::table('users', function (Blueprint $table) {
            $table->date('contract_end_date')->nullable()->after('email');
            $table->string('contract_type')->nullable()->after('contract_end_date'); // e.g., 'fixed-term', 'permanent', 'freelance'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['contract_end_date', 'contract_type']);
        });
    }
};
