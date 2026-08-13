<?php

namespace App\Domain\Genplan;

use App\Domain\Users\Enums\PermissionName;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GenplanContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::GenplanView->value);
    }

    public function view(User $user, Model $record): bool
    {
        return $user->can(PermissionName::GenplanView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::GenplanManage->value);
    }

    public function update(User $user, Model $record): bool
    {
        return $user->can(PermissionName::GenplanManage->value);
    }

    public function delete(User $user, Model $record): bool
    {
        if (! $user->can(PermissionName::GenplanManage->value)) {
            return false;
        }

        return match (true) {
            $record instanceof Genplan => ! $record->quarters()->exists() && ! $record->infrastructurePoints()->exists(),
            $record instanceof Quarter => ! $record->plots()->exists(),
            default => true,
        };
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
