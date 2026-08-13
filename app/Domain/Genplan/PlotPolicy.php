<?php

namespace App\Domain\Genplan;

use App\Domain\Users\Enums\PermissionName;
use App\Models\User;

class PlotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::PlotsView->value);
    }

    public function view(User $user, Plot $plot): bool
    {
        return $user->can(PermissionName::PlotsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::PlotsManage->value);
    }

    public function update(User $user, Plot $plot): bool
    {
        return $user->can(PermissionName::PlotsManage->value);
    }

    public function delete(User $user, Plot $plot): bool
    {
        return $user->can(PermissionName::PlotsManage->value);
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
