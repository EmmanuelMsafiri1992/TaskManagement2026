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
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('quotations', 'color')) {
                $table->string('color')->default('#2568ef')->after('status');
            }
            if (!Schema::hasColumn('quotations', 'logo')) {
                $table->string('logo')->nullable()->after('color');
            }
            if (!Schema::hasColumn('quotations', 'business_name')) {
                $table->string('business_name')->nullable()->after('logo');
            }
            if (!Schema::hasColumn('quotations', 'business_address')) {
                $table->text('business_address')->nullable()->after('business_name');
            }
            if (!Schema::hasColumn('quotations', 'business_phone')) {
                $table->string('business_phone')->nullable()->after('business_address');
            }
            if (!Schema::hasColumn('quotations', 'business_email')) {
                $table->string('business_email')->nullable()->after('business_phone');
            }
        });

        // Create quotation_items table if it doesn't exist
        if (!Schema::hasTable('quotation_items')) {
            Schema::create('quotation_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
                $table->string('description');
                $table->text('details')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
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
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn(['color', 'logo', 'business_name', 'business_address', 'business_phone', 'business_email']);
        });

        Schema::dropIfExists('quotation_items');
    }
};
