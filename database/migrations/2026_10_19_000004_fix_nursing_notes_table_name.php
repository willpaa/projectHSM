<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('i_p_d_s') && ! Schema::hasColumn('i_p_d_s', 'nursing_notes')) {
            Schema::table('i_p_d_s', function (Blueprint $table): void {
                $table->longText('nursing_notes')->nullable()->after('treatment_plan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('i_p_d_s') && Schema::hasColumn('i_p_d_s', 'nursing_notes')) {
            Schema::table('i_p_d_s', function (Blueprint $table): void {
                $table->dropColumn('nursing_notes');
            });
        }
    }
};
