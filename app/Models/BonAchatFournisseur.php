<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonAchatFournisseur extends Model
{
    protected $table = 'bon_achat_fournisseur';
    
    protected $fillable = [
        'numero_bon',
        'date',
        'fournisseur_id',
        'type_paiement',
        'echeance',
        'client_livre',
        'famille',
        'ville',
        'chauffeur',
        'matricule',
        'sous_total_ttc',
        'total_qte',
        'total_ttc',
        'statut',
        'bon_commande_id',
    ];
    
    protected $casts = [
        'date' => 'date',
        'sous_total_ttc' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];
    
    // Relationships
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }
    
    public function articles()
    {
        return $this->hasMany(BonAchatArticle::class, 'bon_achat_id');
    }
    
    public function bonCommande()
    {
        return $this->belongsTo(BonCommandeFournisseur::class, 'bon_commande_id');
    }
}
