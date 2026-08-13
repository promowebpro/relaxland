<?php

namespace App\Domain\Users;

use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionRegistrar
{
    /**
     * @return array<string, list<string>>
     */
    public function matrix(): array
    {
        return [
            RoleName::SuperAdmin->value => array_column(PermissionName::cases(), 'value'),
            RoleName::ContentManager->value => [
                PermissionName::AdminAccess->value,
                PermissionName::ContentView->value,
                PermissionName::ContentCreate->value,
                PermissionName::ContentUpdate->value,
                PermissionName::ContentDelete->value,
                PermissionName::ContentPublish->value,
                PermissionName::SettingsView->value,
            ],
            RoleName::SalesManager->value => [
                PermissionName::AdminAccess->value,
                PermissionName::LeadsView->value,
                PermissionName::LeadsUpdate->value,
            ],
            RoleName::Viewer->value => [
                PermissionName::AdminAccess->value,
                PermissionName::ContentView->value,
                PermissionName::GenplanView->value,
                PermissionName::PlotsView->value,
                PermissionName::SettingsView->value,
            ],
        ];
    }

    public function seed(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->matrix() as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
