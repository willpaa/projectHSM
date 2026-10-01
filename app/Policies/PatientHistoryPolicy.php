<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PatientHistory;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientHistoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PatientHistory');
    }

    public function view(AuthUser $authUser, PatientHistory $patientHistory): bool
    {
        return $authUser->can('View:PatientHistory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PatientHistory');
    }

    public function update(AuthUser $authUser, PatientHistory $patientHistory): bool
    {
        return $authUser->can('Update:PatientHistory');
    }

    public function delete(AuthUser $authUser, PatientHistory $patientHistory): bool
    {
        return $authUser->can('Delete:PatientHistory');
    }

    public function restore(AuthUser $authUser, PatientHistory $patientHistory): bool
    {
        return $authUser->can('Restore:PatientHistory');
    }

    public function forceDelete(AuthUser $authUser, PatientHistory $patientHistory): bool
    {
        return $authUser->can('ForceDelete:PatientHistory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PatientHistory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PatientHistory');
    }

    public function replicate(AuthUser $authUser, PatientHistory $patientHistory): bool
    {
        return $authUser->can('Replicate:PatientHistory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PatientHistory');
    }

}