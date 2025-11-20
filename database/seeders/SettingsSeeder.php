<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default app name
        Setting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => 'Bouyahya']
        );

        // Default cities
        $defaultCities = [
            'Casablanca',
            'Rabat',
            'Marrakech',
            'Fès',
            'Agadir',
            'Tanger',
            'Kenitra',
            'Oujda',
            'Tetouan'
        ];
        
        Setting::updateOrCreate(
            ['key' => 'cities'],
            ['value' => json_encode($defaultCities)]
        );

        // App logo (null by default, to be uploaded by user)
        Setting::updateOrCreate(
            ['key' => 'app_logo'],
            ['value' => null]
        );

        echo "Settings seeded successfully!\n";
    }
}
