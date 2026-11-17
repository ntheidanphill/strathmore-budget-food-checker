<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MENU_ITEMS: the heart of the app. One row = one food item on one vendor's menu.
 * Relationships: vendor -> many items, category -> many items.
 * price is DECIMAL (never float) because it is money.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name', 100);
            $table->decimal('price', 8, 2);                 // KES, e.g. 150.00
            $table->boolean('is_active')->default(true);    // the "IsActive" stock toggle
            $table->timestamps();

            // Business rule at database level: a vendor cannot list the same item twice
            $table->unique(['vendor_id', 'name']);
            // Index speeds up the budget search (R6: results within 3 seconds)
            $table->index(['is_active', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
