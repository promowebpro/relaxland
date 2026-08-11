<?php

namespace Tests\Feature\Foundation;

use App\Domain\Users\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateSuperAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_an_active_super_admin_without_a_password_option(): void
    {
        $this->artisan('app:create-super-admin', [
            '--name' => 'Главный администратор',
            '--email' => 'admin@example.test',
        ])
            ->expectsQuestion('Password (at least 12 characters)', 'a-secure-password')
            ->expectsQuestion('Confirm password', 'a-secure-password')
            ->assertSuccessful();

        $user = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole(RoleName::SuperAdmin->value));
        $this->assertNotNull($user->email_verified_at);
    }
}
