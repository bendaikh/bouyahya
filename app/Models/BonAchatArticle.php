<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonAchatArticle extends Model
{
    protected $table = 'bon_achat_articles';
    
    protected $fillable = [
        'bon_achat_id',
        'ref_article',
        'designation_article',
        'qte',
        'prix_unitaire_ttc',
        'total',
    ];
    
    protected $casts = [
        'prix_unitaire_ttc' => 'decimal:2',
        'total' => 'decimal:2',
    ];
    
    // Relationships
    public function bonAchat()
    {
        return $this->belongsTo(BonAchatFournisseur::class, 'bon_achat_id');
    }
}
