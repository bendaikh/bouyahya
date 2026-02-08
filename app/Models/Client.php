<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'code_client',
        'raison_sociale',
        'nom_gerant',
        'ville',
        'telephone',
        'type_client',
        'mode_paiement',
        'echeance',
        'cin',
        'if_fiscal',
        'patente',
        'cnss',
        'ice',
        'banque',
        'rib',
        'plafond',
        'bloquer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Automatically set user_id when creating
        static::creating(function ($model) {
            if (auth()->check() && !$model->user_id) {
                $model->user_id = auth()->id();
            }
        });
    }

    /**
     * Get the user that created this client
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    protected $casts = [
        'plafond' => 'decimal:2',
        'bloquer' => 'boolean',
    ];
}
