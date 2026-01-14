<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles and permissions first, then superadmin
        $this->call([
            RolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
