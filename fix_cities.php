<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Setting;

$defaultCities = [
    'Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Agadir', 'Tanger', 'Kenitra', 'Oujda', 'Tetouan'
];

$citiesFromClients = Client::distinct()->pluck('ville')->filter()->all();
$citiesFromFournisseurs = Fournisseur::distinct()->pluck('ville')->filter()->all();

$allCities = array_unique(array_merge($citiesFromClients, $citiesFromFournisseurs, $defaultCities));
sort($allCities);

echo "Reconstructed cities list:\n";
$citiesArray = array_values($allCities);
echo json_encode($citiesArray, JSON_PRETTY_PRINT) . "\n";

// Update the database
$setting = Setting::where('key', 'cities')->first();
if ($setting) {
    $setting->update(['value' => json_encode($citiesArray)]);
    echo "\nDatabase updated successfully!\n";
} else {
    Setting::create([
        'key' => 'cities',
        'value' => json_encode($citiesArray)
    ]);
    echo "\nSetting created successfully!\n";
}
