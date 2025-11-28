<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglementFournisseur extends Model
{
    protected $table = 'reglements_fournisseurs';
    
    protected $fillable = [
        'code_reglement',
        'date_reglement',
        'fournisseur_id',
        'type_reglement',
        'numero_piece',
        'banque',
        'nom_beneficiaire',
        'montant',
        'date_encaissement',
        'statut',
        'observation',
    ];
    
    protected $casts = [
        'date_reglement' => 'date',
        'date_encaissement' => 'date',
        'montant' => 'decimal:2',
    ];
    
    // Relations
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }
    
    public function lignes()
    {
        return $this->hasMany(ReglementFournisseurLigne::class, 'reglement_id');
    }
}

