<?php

namespace App\Policies;

use App\Domain\Users\Enums\PermissionName;
use App\Models\BlogCategory;
use App\Models\User;

class BlogCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ContentView->value);
    }

    public function view(User $user, BlogCategory $category): bool
    {
        return $user->can(PermissionName::ContentView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ContentCreate->value);
    }

    public function update(User $user, BlogCategory $category): bool
    {
        return $user->can(PermissionName::ContentUpdate->value);
    }

    public function delete(User $user, BlogCategory $category): bool
    {
        return $user->can(PermissionName::ContentDelete->value) && ! $category->posts()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function publish(User $user, BlogCategory $category): bool
    {
        return $user->can(PermissionName::ContentPublish->value);
    }
}
