<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('legume_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legume_product_id')->constrained('legume_products')->onDelete('cascade');
            $table->enum('alert_type', ['low_stock', 'out_of_stock', 'overstock']);
            $table->decimal('threshold_quantity', 15, 3);
            $table->decimal('current_quantity', 15, 3);
            $table->boolean('is_acknowledged')->default(false);
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['alert_type', 'is_acknowledged']);
            $table->index('legume_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legume_stock_alerts');
    }
};
