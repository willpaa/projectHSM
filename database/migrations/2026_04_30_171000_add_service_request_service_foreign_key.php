<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_requests') || ! Schema::hasTable('services')) {
            return;
        }

        if ($this->foreignKeyExists('service_requests', 'service_requests_service_id_foreign')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_requests')) {
            return;
        }

        if (! $this->foreignKeyExists('service_requests', 'service_requests_service_id_foreign')) {
            return;
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign('service_requests_service_id_foreign');
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};
