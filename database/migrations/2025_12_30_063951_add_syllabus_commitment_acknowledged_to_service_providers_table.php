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
        Schema::table('service_providers', function (Blueprint $table) {
            $table->boolean('syllabus_commitment_acknowledged')->default(false)->after('agreement_signed_at');
            $table->timestamp('syllabus_commitment_acknowledged_at')->nullable()->after('syllabus_commitment_acknowledged');
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
            $table->dropColumn(['syllabus_commitment_acknowledged', 'syllabus_commitment_acknowledged_at']);
        });
    }
};
