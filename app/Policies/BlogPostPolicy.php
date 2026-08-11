<?php

namespace App\Policies;

use App\Domain\Users\Enums\PermissionName;
use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ContentView->value);
    }

    public function view(User $user, BlogPost $post): bool
    {
        return $user->can(PermissionName::ContentView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ContentCreate->value);
    }

    public function update(User $user, BlogPost $post): bool
    {
        return $user->can(PermissionName::ContentUpdate->value);
    }

    public function delete(User $user, BlogPost $post): bool
    {
        return $user->can(PermissionName::ContentDelete->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionName::ContentDelete->value);
    }

    public function publish(User $user, BlogPost $post): bool
    {
        return $user->can(PermissionName::ContentPublish->value);
    }
}
