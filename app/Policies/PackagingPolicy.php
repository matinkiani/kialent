<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Packaging;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PackagingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Packaging');
    }

    public function view(AuthUser $authUser, Packaging $packaging): bool
    {
        return $authUser->can('View:Packaging');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Packaging');
    }

    public function update(AuthUser $authUser, Packaging $packaging): bool
    {
        return $authUser->can('Update:Packaging');
    }

    public function delete(AuthUser $authUser, Packaging $packaging): bool
    {
        return $authUser->can('Delete:Packaging');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Packaging');
    }

    public function restore(AuthUser $authUser, Packaging $packaging): bool
    {
        return $authUser->can('Restore:Packaging');
    }

    public function forceDelete(AuthUser $authUser, Packaging $packaging): bool
    {
        return $authUser->can('ForceDelete:Packaging');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Packaging');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Packaging');
    }

    public function replicate(AuthUser $authUser, Packaging $packaging): bool
    {
        return $authUser->can('Replicate:Packaging');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Packaging');
    }
}
