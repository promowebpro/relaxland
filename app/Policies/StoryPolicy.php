<?php

namespace App\Policies;

use App\Domain\Users\Enums\PermissionName;
use App\Models\Story;
use App\Models\User;

class StoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ContentView->value);
    }

    public function view(User $user, Story $story): bool
    {
        return $user->can(PermissionName::ContentView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ContentCreate->value);
    }

    public function update(User $user, Story $story): bool
    {
        return $user->can(PermissionName::ContentUpdate->value);
    }

    public function delete(User $user, Story $story): bool
    {
        return $user->can(PermissionName::ContentDelete->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionName::ContentDelete->value);
    }
}
