<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToUser;

class ReglementClient extends Model
{
    use BelongsToUser;

    protected $table = 'reglements_clients';
    
    protected $fillable = [
        'user_id',
        'code_reglement',
        'date_reglement',
        'client_id',
        'type_reglement',
        'numero_piece',
        'banque',
        'nom_tire',
        'tresorerie_id',
        'montant',
        'date_encaissement',
        'statut',
        'etat_remboursement',
        'observation',
    ];
    
    protected $casts = [
        'date_reglement' => 'date',
        'date_encaissement' => 'date',
        'montant' => 'decimal:2',
    ];
    
    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function tresorerie()
    {
        return $this->belongsTo(CompteTresorerie::class, 'tresorerie_id');
    }
    
    public function lignes()
    {
        return $this->hasMany(ReglementClientLigne::class, 'reglement_id');
    }
}

