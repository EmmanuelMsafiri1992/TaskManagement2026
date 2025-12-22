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
        Schema::create('adsense_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->index();
            $table->string('country_code', 2)->index();
            $table->string('country_name')->nullable();
            $table->bigInteger('impressions')->default(0);
            $table->bigInteger('clicks')->default(0);
            $table->bigInteger('page_views')->default(0);
            $table->decimal('cpc', 10, 4)->default(0)->comment('Cost Per Click');
            $table->decimal('page_rpm', 10, 4)->default(0)->comment('Page Revenue Per Mille');
            $table->decimal('page_ctr', 10, 4)->default(0)->comment('Page Click-Through Rate');
            $table->decimal('earnings', 10, 2)->default(0);
            $table->timestamps();

            // Unique constraint to prevent duplicate entries
            $table->unique(['report_date', 'country_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('adsense_reports');
    }
};
