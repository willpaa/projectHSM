<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('laboratories') || ! Schema::hasTable('staff')) {
            return;
        }

        Schema::table('laboratories', function (Blueprint $table) {
            if (! Schema::hasColumn('laboratories', 'laboratorist_id')) {
                $table->foreignId('laboratorist_id')
                    ->nullable()
                    ->after('doctor_id')
                    ->constrained('staff')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('laboratories')) {
            return;
        }

        Schema::table('laboratories', function (Blueprint $table) {
            if (Schema::hasColumn('laboratories', 'laboratorist_id')) {
                $table->dropConstrainedForeignId('laboratorist_id');
            }
        });
    }
};
