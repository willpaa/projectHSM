<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('patient_identifier_sequences')) {
            Schema::create('patient_identifier_sequences', function (Blueprint $table): void {
                $table->unsignedTinyInteger('id')->primary();
                $table->unsignedBigInteger('last_number')->default(0);
            });
        }

        $lastNumber = Schema::hasTable('patients') ? DB::table('patients')->count() : 0;

        if (Schema::hasTable('patients')
            && Schema::hasColumn('patients', 'patient_id')
            && Schema::hasColumn('patients', 'mrn')) {
            foreach (DB::table('patients')->select(['patient_id', 'mrn'])->cursor() as $patient) {
                foreach ([$patient->patient_id, $patient->mrn] as $identifier) {
                    if (preg_match('/^(?:PAT|MRN)-(\d+)$/i', (string) $identifier, $matches) === 1) {
                        $lastNumber = max($lastNumber, (int) $matches[1]);
                    }
                }
            }
        }

        $existingNumber = DB::table('patient_identifier_sequences')
            ->where('id', 1)
            ->value('last_number');

        DB::table('patient_identifier_sequences')->updateOrInsert(
            ['id' => 1],
            ['last_number' => max($lastNumber, (int) ($existingNumber ?? 0))],
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('patient_identifier_sequences')) {
            DB::table('patient_identifier_sequences')->where('id', 1)->delete();
        }
    }
};
