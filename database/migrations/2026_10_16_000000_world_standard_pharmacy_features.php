<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elevate the pharmacy module to international standards (FIP Basel
     * Statements on hospital pharmacy + WHO/FIP Good Pharmacy Practice):
     *
     *  - Formulary / drug-master data on inventories (ATC, generic, form,
     *    strength, therapeutic class, Rx-only, controlled substance, storage).
     *  - Cold-chain / storage temperature monitoring.
     *  - Controlled substance register (chain of custody with running balance).
     *  - Adverse drug reaction (ADR) monitoring linked to patients.
     */
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table): void {
            if (! Schema::hasColumn('inventories', 'generic_name')) {
                $table->string('generic_name')->nullable()->after('item_name');
            }
            if (! Schema::hasColumn('inventories', 'atc_code')) {
                $table->string('atc_code', 20)->nullable()->index()->after('generic_name');
            }
            if (! Schema::hasColumn('inventories', 'drug_form')) {
                $table->string('drug_form', 60)->nullable()->after('atc_code');
            }
            if (! Schema::hasColumn('inventories', 'strength')) {
                $table->string('strength', 60)->nullable()->after('drug_form');
            }
            if (! Schema::hasColumn('inventories', 'therapeutic_class')) {
                $table->string('therapeutic_class', 120)->nullable()->index()->after('strength');
            }
            if (! Schema::hasColumn('inventories', 'requires_prescription')) {
                $table->boolean('requires_prescription')->default(true)->after('therapeutic_class');
            }
            if (! Schema::hasColumn('inventories', 'is_controlled')) {
                $table->boolean('is_controlled')->default(false)->after('requires_prescription');
            }
            if (! Schema::hasColumn('inventories', 'storage_condition')) {
                $table->string('storage_condition', 30)->default('ambient')->after('store_location');
            }
        });

        if (! Schema::hasTable('pharmacy_storage_temperatures')) {
            Schema::create('pharmacy_storage_temperatures', function (Blueprint $table): void {
                $table->id();
                $table->string('inventory_item_id', 50)->nullable()->index();
                $table->string('storage_location', 120);
                $table->decimal('temperature_celsius', 5, 2);
                $table->decimal('humidity', 5, 2)->nullable();
                $table->boolean('within_range')->default(true);
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamp('recorded_at')->nullable();
                $table->timestamps();

                $table->foreign('inventory_item_id')->references('item_id')->on('inventories')->cascadeOnUpdate()->nullOnDelete();
            });
        }

        if (! Schema::hasTable('controlled_substance_registers')) {
            Schema::create('controlled_substance_registers', function (Blueprint $table): void {
                $table->id();
                $table->string('inventory_item_id', 50)->index();
                $table->string('batch_no', 80)->nullable();
                $table->string('transaction_type', 30);
                $table->integer('quantity');
                $table->integer('running_balance')->default(0);
                $table->foreignId('patient_id')->nullable()->constrained('patients')->cascadeOnUpdate()->nullOnDelete();
                $table->string('prescription_id', 50)->nullable()->index();
                $table->foreignId('pharmacist_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->string('witness', 120)->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->foreign('inventory_item_id')->references('item_id')->on('inventories')->cascadeOnUpdate()->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('pharmacy_adverse_reactions')) {
            Schema::create('pharmacy_adverse_reactions', function (Blueprint $table): void {
                $table->id();
                $table->string('reaction_code', 50)->unique();
                $table->foreignId('patient_id')->nullable()->constrained('patients')->cascadeOnUpdate()->nullOnDelete();
                $table->string('pharmacy_sale_id', 50)->nullable()->index();
                $table->string('inventory_item_id', 50)->nullable()->index();
                $table->string('medicine_name');
                $table->string('severity', 30)->default('mild');
                $table->text('reaction_description');
                $table->string('outcome', 30)->nullable();
                $table->string('status', 30)->default('reported');
                $table->text('action_taken')->nullable();
                $table->foreignId('reported_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_adverse_reactions');
        Schema::dropIfExists('controlled_substance_registers');
        Schema::dropIfExists('pharmacy_storage_temperatures');

        Schema::table('inventories', function (Blueprint $table): void {
            foreach ([
                'storage_condition',
                'is_controlled',
                'requires_prescription',
                'therapeutic_class',
                'strength',
                'drug_form',
                'atc_code',
                'generic_name',
            ] as $column) {
                if (Schema::hasColumn('inventories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
