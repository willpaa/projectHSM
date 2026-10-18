<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PatientIdentifierGenerator
{
    /**
     * Generate the next unique patient code and medical record number.
     *
     * @return array{patient_id: string, mrn: string}
     */
    public function next(): array
    {
        $number = DB::transaction(function (): int {
            $this->ensureSequenceTableExists();

            $sequence = DB::table('patient_identifier_sequences')
                ->where('id', 1)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $this->seedSequenceFromExistingPatients();

                $sequence = DB::table('patient_identifier_sequences')
                    ->where('id', 1)
                    ->lockForUpdate()
                    ->first();
            }

            if (! $sequence) {
                throw new RuntimeException('Patient identifier sequence is missing after initialization.');
            }

            $nextNumber = (int) $sequence->last_number + 1;

            DB::table('patient_identifier_sequences')
                ->where('id', 1)
                ->update(['last_number' => $nextNumber]);

            return $nextNumber;
        }, attempts: 3);

        $suffix = str_pad((string) $number, 6, '0', STR_PAD_LEFT);

        return [
            'patient_id' => 'PAT-' . $suffix,
            'mrn' => 'MRN-' . $suffix,
        ];
    }

    protected function ensureSequenceTableExists(): void
    {
        if (Schema::hasTable('patient_identifier_sequences')) {
            return;
        }

        Schema::create('patient_identifier_sequences', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedBigInteger('last_number')->default(0);
        });
    }

    protected function seedSequenceFromExistingPatients(): void
    {
        if (! Schema::hasTable('patients')) {
            DB::table('patient_identifier_sequences')->insert([
                'id' => 1,
                'last_number' => 0,
            ]);

            return;
        }

        $lastNumber = 0;

        foreach (DB::table('patients')->select(['patient_id', 'mrn'])->cursor() as $patient) {
            foreach ([$patient->patient_id, $patient->mrn] as $identifier) {
                if (preg_match('/^(?:PAT|MRN)-(\d+)$/i', (string) $identifier, $matches) === 1) {
                    $lastNumber = max($lastNumber, (int) $matches[1]);
                }
            }
        }

        DB::table('patient_identifier_sequences')->insert([
            'id' => 1,
            'last_number' => $lastNumber,
        ]);
    }
}
