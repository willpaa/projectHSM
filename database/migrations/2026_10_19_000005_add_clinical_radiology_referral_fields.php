<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('radiologies')) {
            return;
        }

        Schema::table('radiologies', function (Blueprint $table): void {
            if (! Schema::hasColumn('radiologies', 'body_part')) {
                $table->string('body_part', 120)->nullable();
            }
            if (! Schema::hasColumn('radiologies', 'laterality')) {
                $table->string('laterality', 30)->nullable();
            }
            if (! Schema::hasColumn('radiologies', 'clinical_indication')) {
                $table->longText('clinical_indication')->nullable();
            }
            if (! Schema::hasColumn('radiologies', 'clinical_history')) {
                $table->longText('clinical_history')->nullable();
            }
            if (! Schema::hasColumn('radiologies', 'clinical_question')) {
                $table->longText('clinical_question')->nullable();
            }
            if (! Schema::hasColumn('radiologies', 'urgency')) {
                $table->string('urgency', 20)->default('routine');
            }
            if (! Schema::hasColumn('radiologies', 'pregnancy_status')) {
                $table->string('pregnancy_status', 30)->nullable();
            }
            if (! Schema::hasColumn('radiologies', 'contrast_plan')) {
                $table->string('contrast_plan', 30)->nullable();
            }
            if (! Schema::hasColumn('radiologies', 'contrast_safety_notes')) {
                $table->longText('contrast_safety_notes')->nullable();
            }
            if (! Schema::hasColumn('radiologies', 'mri_safety_notes')) {
                $table->longText('mri_safety_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('radiologies')) {
            return;
        }

        $columns = collect([
            'body_part', 'laterality', 'clinical_indication', 'clinical_history',
            'clinical_question', 'urgency', 'pregnancy_status', 'contrast_plan',
            'contrast_safety_notes', 'mri_safety_notes',
        ])->filter(fn (string $column): bool => Schema::hasColumn('radiologies', $column))->all();

        if ($columns !== []) {
            Schema::table('radiologies', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
