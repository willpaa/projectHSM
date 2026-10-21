<?php

use App\Models\Inventory;
use App\Models\PharmacyAdverseReaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::create('inventories', function (Blueprint $table): void {
        $table->id();
        $table->string('item_id', 50)->unique();
        $table->string('item_name');
        $table->boolean('is_controlled')->default(false);
        $table->timestamps();
    });

    Schema::create('patients', function (Blueprint $table): void {
        $table->id();
        $table->string('full_name')->nullable();
        $table->timestamps();
    });

    Schema::create('pharmacy_adverse_reactions', function (Blueprint $table): void {
        $table->id();
        $table->string('reaction_code', 50)->unique();
        $table->unsignedBigInteger('patient_id')->nullable();
        $table->string('pharmacy_sale_id', 50)->nullable();
        $table->string('inventory_item_id', 50)->nullable();
        $table->string('medicine_name');
        $table->string('severity', 30)->default('mild');
        $table->text('reaction_description');
        $table->string('outcome', 30)->nullable();
        $table->string('status', 30)->default('reported');
        $table->text('action_taken')->nullable();
        $table->unsignedBigInteger('reported_by')->nullable();
        $table->timestamp('occurred_at')->nullable();
        $table->timestamps();
    });
});

test('reporting an adverse reaction auto-generates a unique reaction code', function (): void {
    Inventory::query()->create(['item_id' => 'MED-AMOX', 'item_name' => 'Amoxicillin 500mg']);

    $reaction = PharmacyAdverseReaction::query()->create([
        'patient_id' => 3,
        'inventory_item_id' => 'MED-AMOX',
        'medicine_name' => 'Amoxicillin 500mg',
        'severity' => 'moderate',
        'reaction_description' => 'Skin rash and itching after first dose.',
        'status' => 'reported',
        'reported_by' => 1,
    ]);

    expect($reaction->reaction_code)->toStartWith('ADR-')
        ->and($reaction->severity)->toBe('moderate')
        ->and($reaction->status)->toBe('reported');

    $second = PharmacyAdverseReaction::query()->create([
        'patient_id' => 4,
        'medicine_name' => 'Ibuprofen',
        'reaction_description' => 'Stomach upset.',
    ]);

    expect($second->reaction_code)->not->toBe($reaction->reaction_code);
});
