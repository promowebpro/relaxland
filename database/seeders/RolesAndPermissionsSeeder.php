<?php

namespace Database\Seeders;

use App\Domain\Users\RolePermissionRegistrar;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(RolePermissionRegistrar::class)->seed();
    }
}
