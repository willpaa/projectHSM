<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\ScopesPatientRecords;
use App\Services\PatientHistoryRecorder;

class Prescription extends Model
{
    use ScopesPatientRecords;

    protected static function booted(): void
    {
        static::created(function (self $prescription): void {
            PatientHistoryRecorder::record(
                $prescription->patient_id ? (int) $prescription->patient_id : null,
                'prescription',
                'Prescription created',
                implode(' | ', array_filter([
                    'Prescription ID: ' . $prescription->prescription_id,
                    filled($prescription->diagnosis) ? 'Diagnosis: ' . $prescription->diagnosis : null,
                    $prescription->medicationNames() !== [] ? 'Medicines: ' . implode(', ', $prescription->medicationNames()) : null,
                    'Status: ' . $prescription->status,
                ])),
                $prescription->encounter_id ?: $prescription->prescription_id,
            );
        });

        static::updated(function (self $prescription): void {
            if (! $prescription->wasChanged(['diagnosis', 'medications', 'instructions', 'status', 'notes'])) {
                return;
            }

            PatientHistoryRecorder::record(
                $prescription->patient_id ? (int) $prescription->patient_id : null,
                'prescription',
                'Prescription updated',
                implode(' | ', array_filter([
                    'Prescription ID: ' . $prescription->prescription_id,
                    filled($prescription->diagnosis) ? 'Diagnosis: ' . $prescription->diagnosis : null,
                    $prescription->medicationNames() !== [] ? 'Medicines: ' . implode(', ', $prescription->medicationNames()) : null,
                    'Instructions: ' . $prescription->instructions,
                    'Status: ' . $prescription->status,
                    filled($prescription->notes) ? 'Notes: ' . $prescription->notes : null,
                ])),
                $prescription->encounter_id ?: $prescription->prescription_id,
            );
        });
    }

    protected $fillable = [
        'prescription_id',
        'patient_id',
        'doctor_id',
        'encounter_type',
        'encounter_id',
        'prescribed_date',
        'diagnosis',
        'medications',
        'instructions',
        'notes',
        'status',
    ];

    protected $casts = [
        'prescribed_date' => 'date',
        'medications' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctors::class, 'doctor_id');
    }

    /**
     * Normalise the stored medications into a consistent list of rows.
     *
     * Supports both the modern Repeater format (a list of associative
     * arrays with a "drug" key) and the legacy KeyValue format where the
     * medications column was stored as {"Medicine": "Dose"}.
     *
     * @return array<int, array{drug: string, dosage: ?string, frequency: ?string, duration: ?string, quantity: ?string, instructions: ?string}>
     */
    public function medicationRows(): array
    {
        $medications = $this->medications;

        if (! is_array($medications) || $medications === []) {
            return [];
        }

        $rows = [];

        foreach ($medications as $key => $value) {
            if (is_array($value)) {
                // Modern Repeater format.
                $rows[] = [
                    'drug' => (string) ($value['drug'] ?? ''),
                    'dosage' => $value['dosage'] ?? null,
                    'frequency' => $value['frequency'] ?? null,
                    'duration' => $value['duration'] ?? null,
                    'quantity' => $value['quantity'] ?? null,
                    'instructions' => $value['instructions'] ?? null,
                ];

                continue;
            }

            // Legacy KeyValue format: ["Medicine" => "Dose"].
            $rows[] = [
                'drug' => (string) $key,
                'dosage' => is_scalar($value) ? (string) $value : null,
                'frequency' => null,
                'duration' => null,
                'quantity' => null,
                'instructions' => null,
            ];
        }

        return array_values(array_filter($rows, fn (array $row): bool => filled($row['drug'])));
    }

    /**
     * List of just the prescribed medicine names (used by the pharmacy queue).
     *
     * @return array<int, string>
     */
    public function medicationNames(): array
    {
        return collect($this->medicationRows())
            ->pluck('drug')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function pharmacySales(): HasMany
    {
        return $this->hasMany(Pharmacy::class, 'prescription_sale_id', 'prescription_id');
    }

    public function saleInvoices(): HasMany
    {
        return $this->hasMany(PharmacySale::class, 'prescription_id', 'prescription_id');
    }
}
