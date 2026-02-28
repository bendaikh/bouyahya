<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BonAchatFournisseur;
use App\Models\BonAchatArticle;
use App\Models\Fournisseur;
use App\Models\ReglementFournisseur;
use App\Models\ReglementFournisseurLigne;
use App\Traits\UsesSelectedYear;
use Illuminate\Support\Facades\DB;

class BonAchatFournisseurController extends Controller
{
    use UsesSelectedYear;

    /**
     * Get all bon d'achat (optionally filtered by fournisseur_id)
     */
    public function index(Request $request)
    {
        $selectedYear = $this->getSelectedYear();
        
        $query = BonAchatFournisseur::with(['fournisseur', 'articles'])
            ->whereYear('date', $selectedYear);
        
        // Filter by fournisseur_id if provided
        if ($request->has('fournisseur_id') && $request->fournisseur_id) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }
        
        $bons = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($bons);
    }
    
    /**
     * Get historique data with payment information
     */
    public function historique(Request $request)
    {
        $selectedYear = $this->getSelectedYear();
        
        $bons = BonAchatFournisseur::with(['fournisseur', 'articles'])
            ->whereYear('date', $selectedYear)
            ->where('statut', 'valide')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($bon) {
                // Calculate amount paid from full règlement amounts linked to this bon
                $montantPaye = ReglementFournisseur::whereIn('statut', ['paye', 'cour', 'instance', 'reporte'])
                    ->whereHas('lignes', function ($query) use ($bon) {
                        $query->where('bon_achat_id', $bon->id);
                    })
                    ->sum('montant');
                
                $ttc = floatval($bon->total_ttc);
                $paye = floatval($montantPaye);
                
                // SOLDE: montant restant à payer (TTC > payé) - ce que le client doit encore
                $solde = max($ttc - $paye, 0);
                
                // RELIQUAT: trop-perçu (payé > TTC) - excédent de paiement
                $reliquat = max($paye - $ttc, 0);
                
                $bon->montant_paye = $paye;
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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:brouillon,valide,annule'
        ]);

        $bon = BonAchatFournisseur::findOrFail($id);
        $bon->update(['statut' => $request->statut]);

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
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
    
    /**
     * Get payment details (fiche de paiement) for a bon d'achat
     */
    public function getPaymentDetails($id)
    {
        $bon = BonAchatFournisseur::with(['fournisseur'])->findOrFail($id);
        
        // Get all reglements linked to this bon through lignes
        $reglements = ReglementFournisseurLigne::where('bon_achat_id', $id)
            ->with(['reglement'])
            ->get()
            ->map(function ($ligne) {
                $reglement = $ligne->reglement;
                return [
                    'numero_reglement' => $reglement->code_reglement,
                    'type' => $reglement->type_reglement,
                    'banque' => $reglement->banque,
                    'nom_tire' => $reglement->nom_beneficiaire,
                    'montant' => $ligne->montant_regle,
                    'date_encaissement' => $reglement->date_encaissement,
                    'statut' => $reglement->statut,
                ];
            });
        
        // Calculate total paid amount using full règlement amounts linked to this bon
        $totalPaye = ReglementFournisseur::whereIn('statut', ['paye', 'cour', 'instance', 'reporte'])
            ->whereHas('lignes', function ($query) use ($id) {
                $query->where('bon_achat_id', $id);
            })
            ->sum('montant');
        
        return response()->json([
            'bon' => [
                'numero_bon' => $bon->numero_bon,
                'date' => $bon->date,
                'fournisseur' => $bon->fournisseur->nom_fournisseur ?? 'N/A',
            ],
            'reglements' => $reglements,
            'total_paye' => floatval($totalPaye),
            'total_ttc' => floatval($bon->total_ttc),
            'statut' => $bon->statut,
        ]);
    }
}
