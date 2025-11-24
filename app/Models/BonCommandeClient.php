<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonCommandeClient extends Model
{
    protected $table = 'bon_commande_clients';

    protected $fillable = [
        'numero_bon',
        'date',
        'client_id',
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function articles()
    {
        return $this->hasMany(BonCommandeClientArticle::class, 'bon_commande_client_id');
    }
}

