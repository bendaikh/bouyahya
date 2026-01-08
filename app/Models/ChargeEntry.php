<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargeEntry extends Model
{
    protected $table = 'charge_entries';

    protected $fillable = [
        'reference',
        'date',
        'heure',
        'operateur',
        'compte_caisse_id',
        'type_id',
        'numero',
        'libelle',
        'beneficiaire',
        'montant',
        'observation',
    ];

    protected $casts = [
        'date' => 'date',
        'montant' => 'decimal:2',
    ];

    /**
     * Get the type of this charge
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeCharge::class, 'type_id');
    }

    /**
     * Get the compte caisse for this charge
     */
    public function compteCaisse(): BelongsTo
    {
        return $this->belongsTo(CompteTresorerie::class, 'compte_caisse_id');
    }

    /**
     * Generate a new reference code
     */
    public static function generateReference(): string
    {
        $year = date('Y');
        $lastEntry = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastEntry && preg_match('/CHG-' . $year . '\/(\d+)/', $lastEntry->reference, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('CHG-%s/%04d', $year, $nextNumber);
    }
}





