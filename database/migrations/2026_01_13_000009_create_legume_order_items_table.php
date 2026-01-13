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
        Schema::create('legume_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legume_order_id')->constrained('legume_orders')->onDelete('cascade');
            $table->foreignId('legume_product_id')->constrained('legume_products')->onDelete('cascade');
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->timestamps();

            $table->index('legume_order_id');
            $table->index('legume_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legume_order_items');
    }
};
