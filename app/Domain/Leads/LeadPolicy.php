<?php

namespace App\Domain\Leads;

use App\Domain\Users\Enums\PermissionName;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::LeadsView->value);
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can(PermissionName::LeadsView->value);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can(PermissionName::LeadsUpdate->value);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, Lead $lead): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
