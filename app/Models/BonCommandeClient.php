<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\BelongsToUser;

class BonCommandeClient extends Model
{
    use BelongsToUser;

    protected $table = 'bon_commande_clients';

    protected $fillable = [
        'user_id',
        'numero_bon',
        'date',
        'client_id',
        'fournisseur_id',
        'mode_paiement',
        'echeance',
        'ville_livraison',
        'total_quantites',
        'total_general',
        'statut',
        'motif_annulation',
    ];

    protected $casts = [
        'date' => 'date',
        'total_quantites' => 'integer',
        'total_general' => 'decimal:2',
    ];

    /**
     * Normalize the statut attribute to fix encoding issues
     */
    protected function statut(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Map of corrupted encodings to correct values
                $statusMap = [
                    'ValidÃ©' => 'Validé',
                    'ConvertÃ©' => 'Converti',
                    'AnnulÃ©' => 'Annulé',
                    'Validã©' => 'Validé',
                    'Convertã©' => 'Converti',
                    'Annulã©' => 'Annulé',
                ];
                
                return $statusMap[$value] ?? $value;
            },
        );
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function articles()
    {
        return $this->hasMany(BonCommandeClientArticle::class, 'bon_commande_client_id');
    }

    public function bonLivraison()
    {
        return $this->hasOne(BonLivraisonClient::class, 'bon_commande_id');
    }
}

