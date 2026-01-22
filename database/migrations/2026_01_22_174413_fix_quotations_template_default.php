<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any existing quotations with wrong template values
        DB::table('quotations')
            ->where('template', 'template1')
            ->update(['template' => 'style_1']);

        DB::table('quotations')
            ->where('template', 'template2')
            ->update(['template' => 'style_2']);

        DB::table('quotations')
            ->where('template', 'template3')
            ->update(['template' => 'style_3']);

        DB::table('quotations')
            ->where('template', 'template4')
            ->update(['template' => 'style_4']);

        DB::table('quotations')
            ->where('template', 'template5')
            ->update(['template' => 'style_5']);

        DB::table('quotations')
            ->where('template', 'template6')
            ->update(['template' => 'style_6']);

        DB::table('quotations')
            ->where('template', 'template7')
            ->update(['template' => 'style_7']);

        DB::table('quotations')
            ->where('template', 'template8')
            ->update(['template' => 'style_8']);

        // Change the default value
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('template')->default('style_1')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('template')->default('template1')->change();
        });
    }
};
