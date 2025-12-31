<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeCharge extends Model
{
    protected $table = 'types_charges';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    /**
     * Get all charge entries for this type
     */
    public function chargeEntries(): HasMany
    {
        return $this->hasMany(ChargeEntry::class, 'type_id');
    }
}

