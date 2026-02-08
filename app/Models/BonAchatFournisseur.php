<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ReglementFournisseurLigne;
use App\Traits\BelongsToUser;

class BonAchatFournisseur extends Model
{
    use BelongsToUser;

    protected $table = 'bon_achat_fournisseur';
    
    protected $fillable = [
        'user_id',
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

    protected $appends = ['montant_paye'];

    public function getMontantPayeAttribute()
    {
        return $this->reglementLignes()
            ->join('reglements_fournisseurs', 'reglement_fournisseur_lignes.reglement_id', '=', 'reglements_fournisseurs.id')
            ->whereIn('reglements_fournisseurs.statut', ['paye', 'cour', 'instance'])
            ->sum('reglement_fournisseur_lignes.montant_regle');
    }
    
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

    public function reglementLignes()
    {
        return $this->hasMany(ReglementFournisseurLigne::class, 'bon_achat_id');
    }
}
