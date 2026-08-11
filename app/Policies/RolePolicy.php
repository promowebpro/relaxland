<?php

namespace App\Policies;

use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::RolesView->value);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can(PermissionName::RolesView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::RolesCreate->value);
    }

    public function update(User $user, Role $role): bool
    {
        return $role->name !== RoleName::SuperAdmin->value
            && $user->can(PermissionName::RolesUpdate->value);
    }

    public function delete(User $user, Role $role): bool
    {
        return $role->name !== RoleName::SuperAdmin->value
            && $user->can(PermissionName::RolesDelete->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionName::RolesDelete->value);
    }
}
