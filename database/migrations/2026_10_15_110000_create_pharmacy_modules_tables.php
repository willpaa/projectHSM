<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patient_allergies')) {
            Schema::create('patient_allergies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->nullable()->constrained('patients')->cascadeOnDelete();
                $table->string('allergen', 150);
                $table->string('severity', 20)->default('moderate'); // mild|moderate|severe
                $table->text('reaction_notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pharmacy_stores')) {
            Schema::create('pharmacy_stores', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120)->unique();
                $table->string('location', 200)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['pharmacy_stores', 'patient_allergies'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
