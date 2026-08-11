<?php

namespace App\Console\Commands;

use App\Domain\Users\Enums\RoleName;
use App\Domain\Users\RolePermissionRegistrar;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-super-admin
                            {--name= : Administrator name}
                            {--email= : Administrator email address}';

    protected $description = 'Create an active RelaxLand super administrator securely';

    public function handle(RolePermissionRegistrar $registrar): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Name')));
        $email = mb_strtolower(trim((string) ($this->option('email') ?: $this->ask('Email'))));
        $password = (string) $this->secret('Password (at least 12 characters)');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(12)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($registrar, $name, $email, $password): void {
            $registrar->seed();

            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => $password,
                'is_active' => true,
            ]);

            $user->assignRole(RoleName::SuperAdmin->value);
        });

        $this->info('Super administrator created. You can now sign in at /admin.');

        return self::SUCCESS;
    }
}
