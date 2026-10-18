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
            if (! Schema::hasColumn('radiologies', 'findings')) {
                $table->longText('findings')->nullable();
            }

            if (! Schema::hasColumn('radiologies', 'conclusion')) {
                $table->longText('conclusion')->nullable();
            }

            if (! Schema::hasColumn('radiologies', 'report_image')) {
                $table->string('report_image')->nullable();
            }

            if (! Schema::hasColumn('radiologies', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('radiologies')) {
            return;
        }

        $columns = collect(['findings', 'conclusion', 'report_image', 'completed_at'])
            ->filter(fn (string $column): bool => Schema::hasColumn('radiologies', $column))
            ->all();

        if ($columns !== []) {
            Schema::table('radiologies', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
