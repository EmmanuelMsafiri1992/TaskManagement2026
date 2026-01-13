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
        Schema::create('legume_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legume_product_id')->constrained('legume_products')->onDelete('cascade');
            $table->enum('type', ['purchase', 'sale', 'adjustment', 'damage', 'return']);
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 3);
            $table->morphs('reference');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['legume_product_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legume_inventory_movements');
    }
};
