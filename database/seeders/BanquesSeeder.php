<?php

namespace Database\Seeders;

use App\Models\Banque;
use Illuminate\Database\Seeder;

class BanquesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banques = [
            'Attijariwafa Bank',
            'BMCE Bank',
            'Banque Populaire',
            'BMCI',
            'Société Générale',
            'CIH Bank',
            'Crédit du Maroc',
            'Crédit Agricole du Maroc',
            'Bank Al-Maghrib',
            'Al Barid Bank',
            'Autre',
        ];

        foreach ($banques as $nom) {
            Banque::firstOrCreate(['nom' => $nom]);
        }
    }
}
