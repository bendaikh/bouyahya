<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonCommandeFournisseur extends Model
{
    protected $table = 'bon_commande_fournisseurs';

    protected $fillable = [
        'numero_bon',
        'date',
        'fournisseur_id',
        'mode_paiement',
        'echeance',
        'total_quantites',
        'total_general',
        'statut',
    ];

    protected $casts = [
        'date' => 'date',
        'total_quantites' => 'integer',
        'total_general' => 'decimal:2',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function articles()
    {
        return $this->hasMany(BonCommandeFournisseurArticle::class, 'bon_commande_fournisseur_id');
    }
}

