<?php

namespace App\Domain\Leads;

use App\Domain\Users\Enums\PermissionName;
use App\Models\User;

class AssignableLeadManagers
{
    /** @return array<int, string> */
    public function options(): array
    {
        return User::query()
            ->where('is_active', true)
            ->permission(PermissionName::LeadsUpdate->value)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function contains(?int $userId): bool
    {
        return $userId === null || array_key_exists($userId, $this->options());
    }
}
