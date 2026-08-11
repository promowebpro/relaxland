<?php

namespace App\Policies;

use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::UsersView->value);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(PermissionName::UsersView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::UsersCreate->value);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(PermissionName::UsersUpdate->value);
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->can(PermissionName::UsersDelete->value)) {
            return false;
        }

        if ($user->is($model)) {
            return false;
        }

        if ($model->is_active && $model->hasRole(RoleName::SuperAdmin->value)) {
            return User::query()
                ->where('is_active', true)
                ->role(RoleName::SuperAdmin->value)
                ->whereKeyNot($model->getKey())
                ->exists();
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionName::UsersDelete->value);
    }
}
