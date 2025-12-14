<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglementClientLigne extends Model
{
    protected $table = 'reglement_client_lignes';
    
    protected $fillable = [
        'reglement_id',
        'bon_livraison_id',
        'montant_regle',
    ];
    
    protected $casts = [
        'montant_regle' => 'decimal:2',
    ];
    
    // Relations
    public function reglement()
    {
        return $this->belongsTo(ReglementClient::class, 'reglement_id');
    }
    
    public function bonLivraison()
    {
        return $this->belongsTo(BonLivraisonClient::class, 'bon_livraison_id');
    }
}

