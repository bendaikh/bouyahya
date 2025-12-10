<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonLivraisonClientArticle extends Model
{
    protected $table = 'bon_livraison_client_articles';

    protected $fillable = [
        'bon_livraison_client_id',
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

    public function bonLivraison()
    {
        return $this->belongsTo(BonLivraisonClient::class, 'bon_livraison_client_id');
    }
}

