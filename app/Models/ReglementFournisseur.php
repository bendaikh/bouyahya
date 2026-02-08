<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;

class ReglementFournisseur extends Model
{
    use BelongsToUser;

    protected $table = 'reglements_fournisseurs';
    
    protected $fillable = [
        'user_id',
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
        'etat_remboursement',
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

