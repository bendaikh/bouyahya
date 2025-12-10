<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonLivraisonClient extends Model
{
    protected $table = 'bon_livraison_clients';

    protected $fillable = [
        'numero_bon',
        'date',
        'client_id',
        'bon_commande_id',
        'mode_paiement',
        'echeance',
        'date_echeance',
        'ville_livraison',
        'chauffeur',
        'matricule_vehicule',
        'telephone_chauffeur',
        'adresse_livraison',
        'observations',
        'total_quantites',
        'total_general',
        'statut',
    ];

    protected $casts = [
        'date' => 'date',
        'date_echeance' => 'date',
        'total_quantites' => 'integer',
        'total_general' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function bonCommande()
    {
        return $this->belongsTo(BonCommandeClient::class, 'bon_commande_id');
    }

    public function articles()
    {
        return $this->hasMany(BonLivraisonClientArticle::class, 'bon_livraison_client_id');
    }
}

