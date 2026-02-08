<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $fillable = [
        'user_id',
        'code_fournisseur',
        'nom_fournisseur',
        'nom_gerant',
        'telephone',
        'email',
        'activite',
        'ville',
        'ice',
        'mode_paiement',
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
     * Get the user that created this fournisseur
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
