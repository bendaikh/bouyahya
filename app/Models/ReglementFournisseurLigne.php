<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglementFournisseurLigne extends Model
{
    protected $table = 'reglement_fournisseur_lignes';
    
    protected $fillable = [
        'reglement_id',
        'bon_achat_id',
        'montant_regle',
    ];
    
    protected $casts = [
        'montant_regle' => 'decimal:2',
    ];
    
    // Relations
    public function reglement()
    {
        return $this->belongsTo(ReglementFournisseur::class, 'reglement_id');
    }
    
    public function bonAchat()
    {
        return $this->belongsTo(BonAchatFournisseur::class, 'bon_achat_id');
    }
}

