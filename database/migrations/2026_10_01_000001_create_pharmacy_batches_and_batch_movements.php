<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('inventory_item_id', 50);
            $table->string('batch_no', 80)->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->unsignedInteger('quantity_received');
            $table->unsignedInteger('quantity_remaining')->index();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->string('supplier_id', 50)->nullable();
            $table->string('reference', 120)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign('inventory_item_id')->references('item_id')->on('inventories')->cascadeOnUpdate()->restrictOnDelete();
            $table->index(['inventory_item_id', 'expiry_date', 'quantity_remaining'], 'pharmacy_batches_fefo_idx');
            $table->unique(['inventory_item_id', 'batch_no'], 'pharmacy_batches_item_batch_unique');
        });

        Schema::create('pharmacy_batch_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pharmacy_batch_id')->constrained('pharmacy_batches')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('operation_type', 30);
            $table->integer('quantity_change');
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('pharmacies', function (Blueprint $table): void {
            $table->string('inventory_item_id', 50)->nullable()->index();
            $table->string('prescribed_medicine')->nullable();
            $table->text('dosage_instructions')->nullable();
            $table->string('queue_status', 30)->default('pending')->index();
        });

        Schema::table('pharmacies', function (Blueprint $table): void {
            $table->foreign('inventory_item_id')->references('item_id')->on('inventories')->cascadeOnUpdate()->nullOnDelete();
        });

        foreach (DB::table('pharmacies')->get(['id', 'medicine_id', 'medicine_name']) as $queueItem) {
            $inventoryItemId = DB::table('inventories')->where('item_name', $queueItem->medicine_name)->value('item_id')
                ?? DB::table('inventories')->where('item_id', (string) $queueItem->medicine_id)->value('item_id');

            if ($inventoryItemId !== null) {
                DB::table('pharmacies')->where('id', $queueItem->id)->update(['inventory_item_id' => $inventoryItemId]);
            }
        }

        foreach (DB::table('inventories')->where('quantity', '>', 0)->get(['item_id', 'quantity', 'expiry_date', 'purchase_price', 'supplier_id', 'created_at', 'updated_at']) as $item) {
            $batchId = DB::table('pharmacy_batches')->insertGetId([
                'inventory_item_id' => $item->item_id,
                'batch_no' => 'OPENING-' . substr(sha1((string) $item->item_id), 0, 12),
                'expiry_date' => $item->expiry_date,
                'quantity_received' => $item->quantity,
                'quantity_remaining' => $item->quantity,
                'purchase_price' => $item->purchase_price ?? 0,
                'supplier_id' => $item->supplier_id,
                'reference' => 'Opening stock migration',
                'received_at' => $item->created_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);

            DB::table('pharmacy_batch_movements')->insert([
                'pharmacy_batch_id' => $batchId,
                'operation_type' => 'opening_stock',
                'quantity_change' => $item->quantity,
                'reference' => 'Opening stock migration',
                'notes' => 'Migrated from the existing inventory balance.',
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }

        DB::table('pharmacies')->where('stock_qty', '<=', 0)->update(['queue_status' => 'dispensed']);
        DB::table('pharmacies')->where('stock_qty', '>', 0)->update(['queue_status' => 'pending']);

        foreach (DB::table('pharmacies')->where('stock_qty', '>', 0)->whereNotNull('prescription_sale_id')->distinct()->pluck('prescription_sale_id') as $prescriptionId) {
            DB::table('prescriptions')
                ->where('prescription_id', $prescriptionId)
                ->where('status', 'dispensed')
                ->update(['status' => 'active']);
        }
    }

    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table): void {
            $table->dropForeign(['inventory_item_id']);
            $table->dropIndex(['inventory_item_id']);
            $table->dropIndex(['queue_status']);
            $table->dropColumn(['inventory_item_id', 'prescribed_medicine', 'dosage_instructions', 'queue_status']);
        });

        Schema::dropIfExists('pharmacy_batch_movements');
        Schema::dropIfExists('pharmacy_batches');
    }
};
