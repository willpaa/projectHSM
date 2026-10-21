<?php

use App\Models\ControlledSubstanceRegister;
use App\Models\Inventory;
use App\Services\ControlledSubstanceService;
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

    Schema::create('patients', function (Blueprint $table): void {
        $table->id();
        $table->string('full_name')->nullable();
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('controlled_substance_registers', function (Blueprint $table): void {
        $table->id();
        $table->string('inventory_item_id', 50)->index();
        $table->string('batch_no', 80)->nullable();
        $table->string('transaction_type', 30);
        $table->integer('quantity');
        $table->integer('running_balance')->default(0);
        $table->unsignedBigInteger('patient_id')->nullable();
        $table->string('prescription_id', 50)->nullable();
        $table->unsignedBigInteger('pharmacist_id')->nullable();
        $table->string('witness', 120)->nullable();
        $table->text('notes')->nullable();
        $table->timestamp('occurred_at')->nullable();
        $table->timestamps();
    });
});

function makeControlledInventory(array $attributes = []): Inventory
{
    return Inventory::query()->create(array_merge([
        'item_id' => 'MED-MORPH-001',
        'item_name' => 'Morphine 10mg/ml',
        'quantity' => 100,
        'reorder_level' => 20,
        'is_controlled' => true,
        'storage_condition' => 'cold',
    ], $attributes));
}

test('a controlled substance records receipts and maintains a running balance', function (): void {
    $item = makeControlledInventory();

    ControlledSubstanceService::record($item, 'receipt', 100, pharmacistId: 1);

    expect(ControlledSubstanceService::currentBalance($item))->toBe(100);

    ControlledSubstanceService::record($item, 'receipt', 50, pharmacistId: 1);

    expect(ControlledSubstanceService::currentBalance($item))->toBe(150);
});

test('dispensing a controlled substance reduces the running balance and stores chain-of-custody data', function (): void {
    $item = makeControlledInventory();

    ControlledSubstanceService::record($item, 'receipt', 100, pharmacistId: 1);
    ControlledSubstanceService::record($item, 'dispense', 30, pharmacistId: 1, context: [
        'patient_id' => 5,
        'prescription_id' => 'RX-9001',
        'witness' => 'Nurse Alice',
        'batch_no' => 'BATCH-A',
    ]);

    expect(ControlledSubstanceService::currentBalance($item))->toBe(70);

    $entry = ControlledSubstanceRegister::query()->latest('id')->firstOrFail();

    expect($entry->transaction_type)->toBe('dispense')
        ->and($entry->quantity)->toBe(30)
        ->and($entry->running_balance)->toBe(70)
        ->and($entry->patient_id)->toBe(5)
        ->and($entry->prescription_id)->toBe('RX-9001')
        ->and($entry->witness)->toBe('Nurse Alice')
        ->and($entry->batch_no)->toBe('BATCH-A');
});

test('returns and waste movements adjust the balance in the correct direction', function (): void {
    $item = makeControlledInventory();

    ControlledSubstanceService::record($item, 'receipt', 100, pharmacistId: 1);
    ControlledSubstanceService::record($item, 'return', 10, pharmacistId: 1);
    ControlledSubstanceService::record($item, 'waste', 5, pharmacistId: 1);

    expect(ControlledSubstanceService::currentBalance($item))->toBe(105);
});

test('recording against a non-controlled item is rejected', function (): void {
    $item = makeControlledInventory(['is_controlled' => false]);

    expect(fn () => ControlledSubstanceService::record($item, 'receipt', 10, pharmacistId: 1))
        ->toThrow(RuntimeException::class);
});

test('inventory model exposes controlled, cold-chain and low-stock helpers', function (): void {
    $item = makeControlledInventory(['quantity' => 5]);

    expect($item->isControlled())->toBeTrue()
        ->and($item->requiresColdChain())->toBeTrue()
        ->and($item->isLowStock())->toBeTrue();

    $normal = makeControlledInventory([
        'item_id' => 'MED-PARA-001',
        'item_name' => 'Paracetamol 500mg',
        'is_controlled' => false,
        'storage_condition' => 'ambient',
        'quantity' => 500,
    ]);

    expect($normal->isControlled())->toBeFalse()
        ->and($normal->requiresColdChain())->toBeFalse()
        ->and($normal->isLowStock())->toBeFalse();
});
