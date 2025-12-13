<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompteTresorerie extends Model
{
    protected $table = 'compte_tresoreries';

    protected $fillable = [
        'code',
        'date_creation',
        'libelle',
        'type_compte',
        'agence',
        'ville',
        'adresse',
        'solde_initial',
        'solde_actuel',
    ];

    protected $casts = [
        'date_creation' => 'date',
        'solde_initial' => 'decimal:2',
        'solde_actuel' => 'decimal:2',
    ];
}


