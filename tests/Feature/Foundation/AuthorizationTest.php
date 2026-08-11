<?php

namespace Tests\Feature\Foundation;

use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_guest_is_redirected_to_the_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_active_user_without_admin_access_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_super_admin_can_access_main_filament_routes(): void
    {
        $user = $this->userWithRole(RoleName::SuperAdmin);

        $this->actingAs($user);

        $this->get('/admin')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/roles')->assertOk();
    }

    public function test_inactive_user_cannot_access_admin_even_with_permission(): void
    {
        $user = $this->userWithRole(RoleName::SuperAdmin, false);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_content_manager_has_no_system_role_permissions(): void
    {
        $user = $this->userWithRole(RoleName::ContentManager);

        $this->assertTrue($user->can(PermissionName::ContentPublish->value));
        $this->assertFalse($user->can(PermissionName::RolesView->value));
        $this->assertFalse($user->can(PermissionName::UsersUpdate->value));

        $this->actingAs($user)->get('/admin/roles')->assertForbidden();
    }

    public function test_sales_manager_has_only_foundation_sales_permissions(): void
    {
        $user = $this->userWithRole(RoleName::SalesManager);

        foreach ([
            PermissionName::AdminAccess,
            PermissionName::LeadsView,
            PermissionName::LeadsUpdate,
            PermissionName::GenplanView,
            PermissionName::GenplanManage,
            PermissionName::PlotsView,
            PermissionName::PlotsManage,
        ] as $permission) {
            $this->assertTrue($user->can($permission->value));
        }

        foreach ([
            PermissionName::UsersView,
            PermissionName::RolesView,
            PermissionName::ContentUpdate,
            PermissionName::SettingsManage,
        ] as $permission) {
            $this->assertFalse($user->can($permission->value));
        }
    }

    public function test_viewer_has_no_write_permissions(): void
    {
        $user = $this->userWithRole(RoleName::Viewer);

        $this->assertTrue($user->can(PermissionName::ContentView->value));

        foreach (PermissionName::cases() as $permission) {
            if (str_ends_with($permission->value, '.create')
                || str_ends_with($permission->value, '.update')
                || str_ends_with($permission->value, '.delete')
                || str_ends_with($permission->value, '.manage')
                || str_ends_with($permission->value, '.publish')) {
                $this->assertFalse($user->can($permission->value), $permission->value);
            }
        }
    }

    public function test_current_super_admin_and_protected_role_cannot_be_modified_dangerously(): void
    {
        $user = $this->userWithRole(RoleName::SuperAdmin);
        $role = Role::findByName(RoleName::SuperAdmin->value);

        $this->actingAs($user);

        $this->assertTrue($user->can(PermissionName::UsersDelete->value));
        $this->assertFalse(Gate::forUser($user)->allows('delete', $user));
        $this->assertFalse(Gate::forUser($user)->allows('update', $role));
        $this->assertFalse(UserResource::canDelete($user));
        $this->assertFalse(RoleResource::canEdit($role));

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'is_active' => false,
                'roles' => [],
            ])
            ->call('save');

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole(RoleName::SuperAdmin->value));
    }

    private function userWithRole(RoleName $role, bool $isActive = true): User
    {
        $user = User::factory()->create(['is_active' => $isActive]);
        $user->assignRole($role->value);

        return $user;
    }
}
