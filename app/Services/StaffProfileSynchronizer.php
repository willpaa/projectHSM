<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StaffProfileSynchronizer
{
    /** @var array<string, array{role: string, department: string}> */
    private const STAFF_ROLES = [
        'lab_technician' => ['role' => 'lab_technician', 'department' => 'Laboratory'],
        'lab_staff' => ['role' => 'lab_technician', 'department' => 'Laboratory'],
        'laboratory_staff' => ['role' => 'lab_technician', 'department' => 'Laboratory'],
        'radiology_technician' => ['role' => 'radiology_technician', 'department' => 'Radiology'],
        'radiology_staff' => ['role' => 'radiology_technician', 'department' => 'Radiology'],
        'accountant' => ['role' => 'accountant', 'department' => 'Finance'],
        'cashier' => ['role' => 'accountant', 'department' => 'Finance'],
        'casher' => ['role' => 'accountant', 'department' => 'Finance'],
        'billing_staff' => ['role' => 'accountant', 'department' => 'Finance'],
        'pharmacist' => ['role' => 'pharmacist', 'department' => 'Pharmacy'],
        'pharmacy_staff' => ['role' => 'pharmacist', 'department' => 'Pharmacy'],
        'pharmacy' => ['role' => 'pharmacist', 'department' => 'Pharmacy'],
        'pharmcy' => ['role' => 'pharmacist', 'department' => 'Pharmacy'],
    ];

    public function sync(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $this->syncNurseProfile($user);

            $profile = Staff::query()->where('user_id', $user->id)->first();
            $roleNames = $user->roles()->where('roles.guard_name', 'web')->pluck('roles.name');
            $staffRole = $roleNames->first(fn (string $role): bool => isset(self::STAFF_ROLES[$role]));

            if ($staffRole === null) {
                if ($profile !== null) {
                    $profile->user_id = null;
                    $profile->save();
                }

                return;
            }

            $mapping = self::STAFF_ROLES[$staffRole];
            $profile ??= new Staff(['user_id' => $user->id]);
            $profile->fill([
                'full_name' => $user->name,
                'email' => $user->email,
                'role' => $mapping['role'],
                'department' => $mapping['department'],
                'status' => $user->status === 'inactive' || $user->status === 'blocked' ? 'inactive' : 'active',
            ]);
            $profile->user_id = $user->id;
            $profile->save();
        });
    }

    private function syncNurseProfile(User $user): void
    {
        $roles = $user->roles()->where('roles.guard_name', 'web')->pluck('roles.name');
        $nurseRole = $roles->contains('triage_nurse')
            ? 'triage_nurse'
            : ($roles->contains('nurse') ? 'nurse' : null);

        $profile = Nurse::query()->where('user_id', $user->id)->first();

        if ($profile === null && $nurseRole !== null && filled($user->email)) {
            $profile = Nurse::query()
                ->whereNull('user_id')
                ->whereRaw('LOWER(TRIM(email)) = ?', [strtolower(trim($user->email))])
                ->first();
        }

        if ($nurseRole === null) {
            if ($profile !== null) {
                $profile->user_id = null;
                $profile->save();
            }

            return;
        }

        $profile ??= new Nurse([
            'nurse_id' => null,
            'gender' => 'other',
            'shift' => 'day',
            'status' => 'active',
        ]);

        $profile->fill([
            'full_name' => $user->name,
            'email' => $user->email,
            'role' => $nurseRole,
            'status' => in_array($user->status, ['inactive', 'blocked'], true) ? 'inactive' : 'active',
        ]);
        $profile->user_id = $user->id;
        $profile->save();
    }
}
