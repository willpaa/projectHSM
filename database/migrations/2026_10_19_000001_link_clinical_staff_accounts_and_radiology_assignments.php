<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('staff')) {
            Schema::table('staff', function (Blueprint $table): void {
                if (! Schema::hasColumn('staff', 'user_id')) {
                    $table->foreignId('user_id')
                        ->nullable()
                        ->after('staff_id')
                        ->constrained('users')
                        ->nullOnDelete();
                    $table->unique('user_id');
                }
            });
        }

        if (Schema::hasTable('radiologies') && Schema::hasTable('staff')) {
            Schema::table('radiologies', function (Blueprint $table): void {
                if (! Schema::hasColumn('radiologies', 'radiologist_id')) {
                    $table->foreignId('radiologist_id')
                        ->nullable()
                        ->after('doctor_id')
                        ->constrained('staff')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('radiologies') && Schema::hasColumn('radiologies', 'radiologist_id')) {
            Schema::table('radiologies', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('radiologist_id');
            });
        }

        if (Schema::hasTable('staff') && Schema::hasColumn('staff', 'user_id')) {
            Schema::table('staff', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
