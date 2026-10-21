<?php

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\ReorderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::create('inventories', function (Blueprint $table): void {
        $table->id();
        $table->string('item_id', 50)->unique();
        $table->string('item_name');
        $table->string('generic_name')->nullable();
        $table->string('atc_code', 20)->nullable();
        $table->string('drug_form', 60)->nullable();
        $table->string('strength', 60)->nullable();
        $table->string('therapeutic_class', 120)->nullable();
        $table->boolean('requires_prescription')->default(true);
        $table->boolean('is_controlled')->default(false);
        $table->string('category', 100)->nullable();
        $table->string('unit', 30)->nullable();
        $table->unsignedInteger('quantity')->default(0);
        $table->unsignedInteger('reorder_level')->default(0);
        $table->string('supplier_id', 50)->nullable();
        $table->decimal('purchase_price', 10, 2)->default(0);
        $table->decimal('selling_price', 10, 2)->default(0);
        $table->date('expiry_date')->nullable();
        $table->string('store_location', 120)->nullable();
        $table->string('storage_condition', 30)->default('ambient');
        $table->timestamps();
    });

    Schema::create('suppliers', function (Blueprint $table): void {
        $table->id();
        $table->string('supplier_code', 50)->unique();
        $table->string('name');
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
    });

    Schema::create('purchase_orders', function (Blueprint $table): void {
        $table->id();
        $table->string('po_number', 50)->unique();
        $table->unsignedBigInteger('supplier_id');
        $table->date('order_date');
        $table->date('expected_date')->nullable();
        $table->enum('status', ['draft', 'ordered', 'partial', 'received', 'cancelled'])->default('draft');
        $table->decimal('total_amount', 12, 2)->default(0);
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('received_by')->nullable();
        $table->timestamp('received_at')->nullable();
        $table->timestamps();
    });

    Schema::create('purchase_order_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('purchase_order_id');
        $table->string('inventory_item_id', 50);
        $table->unsignedInteger('quantity_ordered')->default(0);
        $table->unsignedInteger('quantity_received')->default(0);
        $table->decimal('unit_cost', 10, 2)->default(0);
        $table->decimal('line_total', 12, 2)->default(0);
        $table->timestamps();
    });
});

test('low stock detection returns only items at or below their reorder level', function (): void {
    Supplier::query()->create(['supplier_code' => 'SUP-001', 'name' => 'Acme Pharma']);

    Inventory::query()->create([
        'item_id' => 'MED-A', 'item_name' => 'Amoxicillin', 'quantity' => 5, 'reorder_level' => 20, 'supplier_id' => 1, 'purchase_price' => 2.5,
    ]);
    Inventory::query()->create([
        'item_id' => 'MED-B', 'item_name' => 'Ibuprofen', 'quantity' => 500, 'reorder_level' => 50, 'supplier_id' => 1, 'purchase_price' => 1.0,
    ]);

    $low = ReorderService::lowStockItems();

    expect($low)->toHaveCount(1)
        ->and($low->first()->item_id)->toBe('MED-A');
});

test('draft purchase orders are generated per supplier from low stock', function (): void {
    $supplier = Supplier::query()->create(['supplier_code' => 'SUP-001', 'name' => 'Acme Pharma']);

    Inventory::query()->create([
        'item_id' => 'MED-A', 'item_name' => 'Amoxicillin', 'quantity' => 4, 'reorder_level' => 10, 'supplier_id' => $supplier->id, 'purchase_price' => 2.0,
    ]);

    $orders = ReorderService::generateDraftPurchaseOrders(userId: 1);

    expect($orders)->toHaveCount(1);

    $order = $orders->first();

    expect($order->status)->toBe('draft')
        ->and($order->supplier_id)->toBe($supplier->id)
        ->and($order->items)->toHaveCount(1);

    // Suggested quantity brings stock up to twice the reorder level: (10*2) - 4 = 16
    expect($order->items->first()->quantity_ordered)->toBe(16)
        ->and((float) $order->total_amount)->toEqual(32.0);
});

test('generating reorder orders is a no-op error when nothing is low on stock', function (): void {
    Supplier::query()->create(['supplier_code' => 'SUP-001', 'name' => 'Acme Pharma']);

    Inventory::query()->create([
        'item_id' => 'MED-B', 'item_name' => 'Ibuprofen', 'quantity' => 500, 'reorder_level' => 50, 'supplier_id' => 1, 'purchase_price' => 1.0,
    ]);

    expect(fn () => ReorderService::generateDraftPurchaseOrders(userId: 1))
        ->toThrow(RuntimeException::class);

    expect(PurchaseOrder::query()->count())->toBe(0);
});
