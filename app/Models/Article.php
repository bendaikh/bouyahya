<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'reference',
        'designation',
        'famille_id',
        'sous_famille_id',
        'unite_mesure_id',
        'tva',
        'autoriser_stock_negatif',
        'prix_achat',
        'prix_vente',
        'stock_actuel',
        'stock_minimum',
        'actif',
    ];

    protected $casts = [
        'tva' => 'decimal:2',
        'prix_achat' => 'decimal:2',
        'prix_vente' => 'decimal:2',
        'autoriser_stock_negatif' => 'boolean',
        'actif' => 'boolean',
    ];

    /**
     * Generate the next article reference
     */
    public static function generateNextReference(): string
    {
        $lastArticle = self::orderBy('id', 'desc')->first();
        
        if (!$lastArticle) {
            return 'ART-0001';
        }
        
        // Extract the number from the reference
        $lastRef = $lastArticle->reference;
        if (preg_match('/ART-(\d+)/', $lastRef, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
            return 'ART-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
        
        return 'ART-0001';
    }

    /**
     * Get famille name from settings
     */
    public function getFamilleNomAttribute(): ?string
    {
        if (!$this->famille_id) return null;
        
        $familles = json_decode(Setting::getValue('familles_article', '[]'), true);
        foreach ($familles as $famille) {
            if ($famille['id'] === $this->famille_id) {
                return $famille['nom'];
            }
        }
        return null;
    }

    /**
     * Get sous-famille name from settings
     */
    public function getSousFamilleNomAttribute(): ?string
    {
        if (!$this->sous_famille_id) return null;
        
        $sousFamilles = json_decode(Setting::getValue('sous_familles_article', '[]'), true);
        foreach ($sousFamilles as $sousFamille) {
            if ($sousFamille['id'] === $this->sous_famille_id) {
                return $sousFamille['nom'];
            }
        }
        return null;
    }

    /**
     * Get unite mesure from settings
     */
    public function getUniteMesureAttribute(): ?array
    {
        if (!$this->unite_mesure_id) return null;
        
        $unites = json_decode(Setting::getValue('unites_mesure', '[]'), true);
        foreach ($unites as $unite) {
            if ($unite['id'] === $this->unite_mesure_id) {
                return $unite;
            }
        }
        return null;
    }
}
