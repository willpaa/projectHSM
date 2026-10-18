<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->index(['doctor_id', 'date', 'status']);
            $table->index(['patient_id', 'date', 'status']);
            $table->string('status', 32)->default('scheduled')->change();
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::table('appointments')
            ->whereIn('status', ['requested', 'checked_in', 'in_progress'])
            ->exists()) {
            throw new \RuntimeException('Cannot roll back while appointments use the expanded workflow statuses.');
        }

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex(['doctor_id', 'date', 'status']);
            $table->dropIndex(['patient_id', 'date', 'status']);
            $table->dropColumn('duration_minutes');
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])
                ->default('scheduled')
                ->change();
        });
    }
};
