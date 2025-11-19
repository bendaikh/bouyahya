<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $fillable = [
        'code_fournisseur',
        'nom_fournisseur',
        'nom_gerant',
        'telephone',
        'email',
        'activite',
        'ville',
        'ice',
        'mode_paiement',
    ];
}
