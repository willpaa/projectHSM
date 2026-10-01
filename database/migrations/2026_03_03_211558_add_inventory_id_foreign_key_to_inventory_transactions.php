<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_transactions') || ! Schema::hasTable('inventories')) {
            return;
        }

        $hasInventoryForeignKey = collect(Schema::getForeignKeys('inventory_transactions'))
            ->contains(fn (array $foreignKey): bool => ($foreignKey['name'] ?? null) === 'inventory_transactions_inventory_id_foreign');

        if ($hasInventoryForeignKey) {
            return;
        }

        Schema::table('inventory_transactions', function (Blueprint $table): void {
            $table->foreign('inventory_id')
                ->references('item_id')
                ->on('inventories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        $hasInventoryForeignKey = collect(Schema::getForeignKeys('inventory_transactions'))
            ->contains(fn (array $foreignKey): bool => ($foreignKey['name'] ?? null) === 'inventory_transactions_inventory_id_foreign');

        if ($hasInventoryForeignKey) {
            Schema::table('inventory_transactions', function (Blueprint $table): void {
                $table->dropForeign('inventory_transactions_inventory_id_foreign');
            });
        }
    }
};