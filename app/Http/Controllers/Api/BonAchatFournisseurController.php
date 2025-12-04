<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BonAchatFournisseur;
use App\Models\BonAchatArticle;
use App\Models\Fournisseur;
use App\Models\ReglementFournisseurLigne;
use Illuminate\Support\Facades\DB;

class BonAchatFournisseurController extends Controller
{
    /**
     * Get all bon d'achat
     */
    public function index()
    {
        $bons = BonAchatFournisseur::with(['fournisseur', 'articles'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($bons);
    }
    
    /**
     * Get historique data with payment information
     */
    public function historique(Request $request)
    {
        $bons = BonAchatFournisseur::with(['fournisseur', 'articles'])
            ->where('statut', 'valide')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bon) {
                // Calculate amount paid from reglements (count all reglements regardless of status)
                $montantPaye = ReglementFournisseurLigne::where('bon_achat_id', $bon->id)
                    ->sum('montant_regle');
                
                // Calculate solde (unpaid balance)
                $solde = floatval($bon->total_ttc) - floatval($montantPaye);
                
                // For reliquat, you can customize this based on your business logic
                // Here we use the same as solde, but you might want to track 
                // items not yet delivered separately
                $reliquat = $solde > 0 ? $solde : 0;
                
                $bon->montant_paye = floatval($montantPaye);
                $bon->solde = $solde;
                $bon->reliquat = $reliquat;
                
                return $bon;
            });
        
        return response()->json($bons);
    }
    
    /**
     * Get a single bon d'achat
     */
    public function show($id)
    {
        $bon = BonAchatFournisseur::with(['fournisseur', 'articles'])->findOrFail($id);
        return response()->json($bon);
    }
    
    /**
     * Generate next numero_bon
     */
    public function nextNumeroBon()
    {
        $year = date('Y');
        $lastBon = BonAchatFournisseur::where('numero_bon', 'like', "BF-{$year}%")
            ->orderBy('numero_bon', 'desc')
            ->first();
        
        if ($lastBon) {
            $lastNumber = intval(substr($lastBon->numero_bon, -3));
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }
        
        return response()->json(['numero_bon' => "BF-{$year}{$nextNumber}"]);
    }
    
    /**
     * Create a new bon d'achat
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_bon' => 'required|unique:bon_achat_fournisseur',
            'date' => 'required|date',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'type_paiement' => 'required|string',
            'echeance' => 'required|string',
            'client_livre' => 'nullable|string',
            'famille' => 'required|string',
            'ville' => 'nullable|string',
            'chauffeur' => 'nullable|string',
            'matricule' => 'nullable|string',
            'articles' => 'required|array|min:1',
            'articles.*.ref_article' => 'required|string',
            'articles.*.designation_article' => 'required|string',
            'articles.*.qte' => 'required|integer|min:1',
            'articles.*.prix_unitaire_ttc' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            // Calculate totals
            $totalQte = 0;
            $totalTtc = 0;
            
            foreach ($validated['articles'] as $article) {
                $totalQte += $article['qte'];
                $totalTtc += $article['qte'] * $article['prix_unitaire_ttc'];
            }
            
            // Create bon d'achat
            $bon = BonAchatFournisseur::create([
                'numero_bon' => $validated['numero_bon'],
                'date' => $validated['date'],
                'fournisseur_id' => $validated['fournisseur_id'],
                'type_paiement' => $validated['type_paiement'],
                'echeance' => $validated['echeance'],
                'client_livre' => $validated['client_livre'],
                'famille' => $validated['famille'],
                'ville' => $validated['ville'],
                'chauffeur' => $validated['chauffeur'],
                'matricule' => $validated['matricule'],
                'sous_total_ttc' => $totalTtc,
                'total_qte' => $totalQte,
                'total_ttc' => $totalTtc,
                'statut' => 'brouillon',
            ]);
            
            // Create articles
            foreach ($validated['articles'] as $article) {
                $bon->articles()->create([
                    'ref_article' => $article['ref_article'],
                    'designation_article' => $article['designation_article'],
                    'qte' => $article['qte'],
                    'prix_unitaire_ttc' => $article['prix_unitaire_ttc'],
                    'total' => $article['qte'] * $article['prix_unitaire_ttc'],
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Bon d\'achat créé avec succès',
                'bon' => $bon->load(['fournisseur', 'articles'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la création: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Update an existing bon d'achat
     */
    public function update(Request $request, $id)
    {
        $bon = BonAchatFournisseur::findOrFail($id);
        
        $validated = $request->validate([
            'date' => 'required|date',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'type_paiement' => 'required|string',
            'echeance' => 'required|string',
            'client_livre' => 'nullable|string',
            'famille' => 'required|string',
            'ville' => 'nullable|string',
            'chauffeur' => 'nullable|string',
            'matricule' => 'nullable|string',
            'articles' => 'required|array|min:1',
            'articles.*.ref_article' => 'required|string',
            'articles.*.designation_article' => 'required|string',
            'articles.*.qte' => 'required|integer|min:1',
            'articles.*.prix_unitaire_ttc' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            // Calculate totals
            $totalQte = 0;
            $totalTtc = 0;
            
            foreach ($validated['articles'] as $article) {
                $totalQte += $article['qte'];
                $totalTtc += $article['qte'] * $article['prix_unitaire_ttc'];
            }
            
            // Update bon d'achat
            $bon->update([
                'date' => $validated['date'],
                'fournisseur_id' => $validated['fournisseur_id'],
                'type_paiement' => $validated['type_paiement'],
                'echeance' => $validated['echeance'],
                'client_livre' => $validated['client_livre'],
                'famille' => $validated['famille'],
                'ville' => $validated['ville'],
                'chauffeur' => $validated['chauffeur'],
                'matricule' => $validated['matricule'],
                'sous_total_ttc' => $totalTtc,
                'total_qte' => $totalQte,
                'total_ttc' => $totalTtc,
            ]);
            
            // Delete old articles and create new ones
            $bon->articles()->delete();
            foreach ($validated['articles'] as $article) {
                $bon->articles()->create([
                    'ref_article' => $article['ref_article'],
                    'designation_article' => $article['designation_article'],
                    'qte' => $article['qte'],
                    'prix_unitaire_ttc' => $article['prix_unitaire_ttc'],
                    'total' => $article['qte'] * $article['prix_unitaire_ttc'],
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Bon d\'achat modifié avec succès',
                'bon' => $bon->load(['fournisseur', 'articles'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la modification: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Validate a bon d'achat
     */
    public function validateBon($id)
    {
        $bon = BonAchatFournisseur::findOrFail($id);
        $bon->update(['statut' => 'valide']);
        
        return response()->json([
            'message' => 'Bon d\'achat validé avec succès',
            'bon' => $bon->load(['fournisseur', 'articles'])
        ]);
    }
    
    /**
     * Cancel a bon d'achat
     */
    public function cancel($id)
    {
        $bon = BonAchatFournisseur::findOrFail($id);
        $bon->update(['statut' => 'annule']);
        
        return response()->json([
            'message' => 'Bon d\'achat annulé avec succès',
            'bon' => $bon->load(['fournisseur', 'articles'])
        ]);
    }
    
    /**
     * Delete a bon d'achat
     */
    public function destroy($id)
    {
        $bon = BonAchatFournisseur::findOrFail($id);
        $bon->delete();
        
        return response()->json(['message' => 'Bon d\'achat supprimé avec succès']);
    }
}
