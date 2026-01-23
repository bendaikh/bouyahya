<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'code_client',
        'raison_sociale',
        'nom_gerant',
        'ville',
        'telephone',
        'type_client',
        'mode_paiement',
        'echeance',
        'cin',
        'if_fiscal',
        'patente',
        'cnss',
        'ice',
        'banque',
        'rib',
        'plafond',
        'bloquer',
    ];

    protected $casts = [
        'plafond' => 'decimal:2',
        'bloquer' => 'boolean',
    ];
}
