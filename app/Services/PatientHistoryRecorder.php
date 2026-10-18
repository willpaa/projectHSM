<?php

namespace App\Services;

use App\Models\PatientHistory;
use Illuminate\Support\Facades\Auth;

class PatientHistoryRecorder
{
    public static function record(
        ?int $patientId,
        string $source,
        string $activity,
        ?string $details = null,
        ?string $encounterId = null,
        ?int $createdBy = null,
    ): ?PatientHistory {
        if (! $patientId) {
            return null;
        }

        return PatientHistory::query()->create([
            'patient_id' => $patientId,
            'encounter_id' => $encounterId,
            'source' => $source,
            'activity' => $activity,
            'details' => $details,
            'occurred_at' => now(),
            'created_by' => $createdBy ?? Auth::id(),
        ]);
    }
}
