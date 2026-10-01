<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PharmacyMovement;
use Illuminate\Auth\Access\HandlesAuthorization;

class PharmacyMovementPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PharmacyMovement');
    }

    public function view(AuthUser $authUser, PharmacyMovement $pharmacyMovement): bool
    {
        return $authUser->can('View:PharmacyMovement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PharmacyMovement');
    }

    public function update(AuthUser $authUser, PharmacyMovement $pharmacyMovement): bool
    {
        return $authUser->can('Update:PharmacyMovement');
    }

    public function delete(AuthUser $authUser, PharmacyMovement $pharmacyMovement): bool
    {
        return $authUser->can('Delete:PharmacyMovement');
    }

    public function restore(AuthUser $authUser, PharmacyMovement $pharmacyMovement): bool
    {
        return $authUser->can('Restore:PharmacyMovement');
    }

    public function forceDelete(AuthUser $authUser, PharmacyMovement $pharmacyMovement): bool
    {
        return $authUser->can('ForceDelete:PharmacyMovement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PharmacyMovement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PharmacyMovement');
    }

    public function replicate(AuthUser $authUser, PharmacyMovement $pharmacyMovement): bool
    {
        return $authUser->can('Replicate:PharmacyMovement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PharmacyMovement');
    }

}