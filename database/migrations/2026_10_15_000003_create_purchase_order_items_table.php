<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_order_items')) {
            return;
        }

        Schema::create('purchase_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            // inventory item reference (inventories uses a string primary key "item_id")
            $table->string('inventory_item_id', 50);
            $table->unsignedInteger('quantity_ordered')->default(0);
            $table->unsignedInteger('quantity_received')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['purchase_order_id', 'inventory_item_id'], 'poi_po_item_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
