<?php

namespace App\Models\Concerns;

use App\Support\PatientPortal;
use Illuminate\Database\Eloquent\Builder;

trait ScopesPatientRecords
{
    protected static function patientOwnershipColumn(): string
    {
        return 'patient_id';
    }

    protected static function bootScopesPatientRecords(): void
    {
        static::addGlobalScope('authenticated_patient', function (Builder $query): void {
            if (! PatientPortal::isPatientUser()) {
                return;
            }

            $patientId = PatientPortal::currentPatientId();

            if (! $patientId) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where(
                $query->getModel()->qualifyColumn(static::patientOwnershipColumn()),
                $patientId,
            );
        });
    }
}
