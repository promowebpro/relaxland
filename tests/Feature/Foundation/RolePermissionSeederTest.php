<?php

namespace Tests\Feature\Foundation;

use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Domain\Users\RolePermissionRegistrar;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_are_seeded_idempotently(): void
    {
        $registrar = app(RolePermissionRegistrar::class);

        $registrar->seed();
        $registrar->seed();

        $this->assertSame(count(RoleName::cases()), Role::query()->count());
        $this->assertSame(count(PermissionName::cases()), Permission::query()->count());
        $this->assertSame(
            count(PermissionName::cases()),
            Role::findByName(RoleName::SuperAdmin->value)->permissions()->count(),
        );
    }

    public function test_each_foundation_role_matches_the_declared_matrix(): void
    {
        $registrar = app(RolePermissionRegistrar::class);
        $registrar->seed();

        foreach ($registrar->matrix() as $roleName => $expectedPermissions) {
            $actualPermissions = Role::findByName($roleName)
                ->permissions()
                ->pluck('name')
                ->sort()
                ->values()
                ->all();

            sort($expectedPermissions);

            $this->assertSame($expectedPermissions, $actualPermissions, $roleName);
        }
    }
}
