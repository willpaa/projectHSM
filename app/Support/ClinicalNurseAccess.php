<?php

namespace App\Support;

use App\Models\Nurse;
use App\Models\Triage;
use Illuminate\Support\Facades\Schema;

class ClinicalNurseAccess
{
    public static function isNurse(): bool
    {
        return auth()->user()?->hasRole('nurse') ?? false;
    }

    public static function isTriageNurse(): bool
    {
        return auth()->user()?->hasRole('triage_nurse') ?? false;
    }

    public static function currentNurseId(?string $role = null): ?int
    {
        $user = auth()->user();

        if (! $user || ! Schema::hasColumn('nurses', 'user_id')) {
            return null;
        }

        $query = Nurse::query();

        if ($role !== null) {
            $query->where('role', $role);
        }

        $id = (clone $query)->where('user_id', $user->id)->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        return $query
            ->whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim((string) $user->email))])
            ->value('id');
    }

    public static function canAccessTriage(Triage $triage): bool
    {
        if (auth()->user()?->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        return static::isTriageNurse()
            && static::currentNurseId('triage_nurse') !== null
            && (int) $triage->nurse_id === static::currentNurseId('triage_nurse');
    }
}
