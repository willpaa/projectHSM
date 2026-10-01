<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PharmacySale;
use Illuminate\Auth\Access\HandlesAuthorization;

class PharmacySalePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PharmacySale');
    }

    public function view(AuthUser $authUser, PharmacySale $pharmacySale): bool
    {
        return $authUser->can('View:PharmacySale');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PharmacySale');
    }

    public function update(AuthUser $authUser, PharmacySale $pharmacySale): bool
    {
        return $authUser->can('Update:PharmacySale');
    }

    public function delete(AuthUser $authUser, PharmacySale $pharmacySale): bool
    {
        return $authUser->can('Delete:PharmacySale');
    }

    public function restore(AuthUser $authUser, PharmacySale $pharmacySale): bool
    {
        return $authUser->can('Restore:PharmacySale');
    }

    public function forceDelete(AuthUser $authUser, PharmacySale $pharmacySale): bool
    {
        return $authUser->can('ForceDelete:PharmacySale');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PharmacySale');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PharmacySale');
    }

    public function replicate(AuthUser $authUser, PharmacySale $pharmacySale): bool
    {
        return $authUser->can('Replicate:PharmacySale');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PharmacySale');
    }

}