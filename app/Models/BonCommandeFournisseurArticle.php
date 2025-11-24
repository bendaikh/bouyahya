<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonCommandeFournisseurArticle extends Model
{
    protected $table = 'bon_commande_fournisseur_articles';

    protected $fillable = [
        'bon_commande_fournisseur_id',
        'code_article',
        'designation',
        'quantite',
        'prix_unitaire',
        'sous_total',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'prix_unitaire' => 'decimal:2',
        'sous_total' => 'decimal:2',
    ];

    public function bonCommande()
    {
        return $this->belongsTo(BonCommandeFournisseur::class, 'bon_commande_fournisseur_id');
    }
}

