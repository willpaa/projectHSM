<?php

namespace App\Support;

use App\Models\Laboratory;
use App\Models\Radiology;
use App\Models\Staff;
use Illuminate\Support\Facades\Schema;

class ClinicalStaffAccess
{
    private const LAB_USER_ROLES = ['lab_technician', 'lab_staff', 'laboratory_staff'];

    private const RADIOLOGY_USER_ROLES = ['radiology_technician', 'radiology_staff'];

    public static function isLabStaff(): bool
    {
        return auth()->user()?->hasAnyRole(self::LAB_USER_ROLES) ?? false;
    }

    public static function isRadiologyStaff(): bool
    {
        return auth()->user()?->hasAnyRole(self::RADIOLOGY_USER_ROLES) ?? false;
    }

    public static function isClinicalStaff(): bool
    {
        return static::isLabStaff() || static::isRadiologyStaff();
    }

    public static function currentLaboratoristId(): ?int
    {
        if (! static::isLabStaff()) {
            return null;
        }

        return static::findStaffProfile('lab_technician');
    }

    public static function currentRadiologistId(): ?int
    {
        if (! static::isRadiologyStaff()) {
            return null;
        }

        return static::findStaffProfile('radiology_technician');
    }

    public static function canManageLaboratory(?Laboratory $record = null): bool
    {
        if (! static::isLabStaff()) {
            return false;
        }

        $staffId = static::currentLaboratoristId();

        return $staffId !== null
            && ($record === null || (int) $record->laboratorist_id === $staffId);
    }

    public static function canManageRadiology(?Radiology $record = null): bool
    {
        if (! static::isRadiologyStaff()) {
            return false;
        }

        $staffId = static::currentRadiologistId();

        return $staffId !== null
            && ($record === null || (int) $record->radiologist_id === $staffId);
    }

    private static function findStaffProfile(string $staffRole): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $query = Staff::query()->where('role', $staffRole);

        if (static::staffTableHasUserId()) {
            $id = (clone $query)->where('user_id', $user->id)->value('id');

            if ($id !== null) {
                return (int) $id;
            }
        }

        return $query
            ->whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim((string) $user->email))])
            ->value('id');
    }

    private static function staffTableHasUserId(): bool
    {
        return Schema::hasColumn('staff', 'user_id');
    }
}
