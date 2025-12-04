<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReglementFournisseur;
use App\Models\ReglementFournisseurLigne;
use App\Models\BonAchatFournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReglementFournisseurController extends Controller
{
    /**
     * Liste tous les règlements fournisseurs
     */
    public function index()
    {
        $reglements = ReglementFournisseur::with(['fournisseur', 'lignes.bonAchat'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($reglements);
    }

    /**
     * Créer un nouveau règlement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_reglement' => 'required|date',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'type_reglement' => 'required|string',
            'numero_piece' => 'nullable|string',
            'banque' => 'nullable|string',
            'nom_beneficiaire' => 'nullable|string',
            'montant' => 'required|numeric|min:0',
            'date_encaissement' => 'nullable|date',
            'observation' => 'nullable|string',
            'statut' => 'nullable|string|in:instance,paye,reporte,cour,impaye,devalide',
            'lignes' => 'nullable|array',
            'lignes.*.bon_achat_id' => 'required_with:lignes|exists:bon_achat_fournisseur,id',
            'lignes.*.montant_regle' => 'required_with:lignes|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Générer le code règlement
            $lastReglement = ReglementFournisseur::orderBy('id', 'desc')->first();
            $nextNumber = $lastReglement ? intval(substr($lastReglement->code_reglement, 3)) + 1 : 1;
            $codeReglement = 'RF-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $reglement = ReglementFournisseur::create([
                'code_reglement' => $codeReglement,
                'date_reglement' => $validated['date_reglement'],
                'fournisseur_id' => $validated['fournisseur_id'],
                'type_reglement' => $validated['type_reglement'],
                'numero_piece' => $validated['numero_piece'] ?? null,
                'banque' => $validated['banque'] ?? null,
                'nom_beneficiaire' => $validated['nom_beneficiaire'] ?? null,
                'montant' => $validated['montant'],
                'date_encaissement' => $validated['date_encaissement'] ?? null,
                'observation' => $validated['observation'] ?? null,
                'statut' => $validated['statut'] ?? 'impaye',
            ]);

            // Créer les lignes de ventilation
            if (!empty($validated['lignes'])) {
                foreach ($validated['lignes'] as $ligne) {
                    ReglementFournisseurLigne::create([
                        'reglement_id' => $reglement->id,
                        'bon_achat_id' => $ligne['bon_achat_id'],
                        'montant_regle' => $ligne['montant_regle'],
                    ]);
                }
            }

            DB::commit();
            
            return response()->json($reglement->load(['fournisseur', 'lignes.bonAchat']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la création: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Afficher un règlement spécifique
     */
    public function show($id)
    {
        $reglement = ReglementFournisseur::with(['fournisseur', 'lignes.bonAchat'])
            ->findOrFail($id);
        
        // Add calculated fields for each bon_achat in lignes
        $reglement->lignes->each(function ($ligne) use ($id) {
            if ($ligne->bonAchat) {
                // Calculate total paid for this bon (excluding current reglement)
                $montantRegleAutres = ReglementFournisseurLigne::where('bon_achat_id', $ligne->bon_achat_id)
                    ->where('reglement_id', '!=', $id)
                    ->sum('montant_regle');
                
                $ligne->bonAchat->montant_regle = floatval($montantRegleAutres);
                $ligne->bonAchat->solde_restant = floatval($ligne->bonAchat->total_ttc) - floatval($montantRegleAutres);
            }
        });
        
        return response()->json($reglement);
    }

    /**
     * Mettre à jour un règlement
     */
    public function update(Request $request, $id)
    {
        $reglement = ReglementFournisseur::findOrFail($id);

        $validated = $request->validate([
            'date_reglement' => 'required|date',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'type_reglement' => 'required|string',
            'numero_piece' => 'nullable|string',
            'banque' => 'nullable|string',
            'nom_beneficiaire' => 'nullable|string',
            'montant' => 'required|numeric|min:0',
            'date_encaissement' => 'nullable|date',
            'observation' => 'nullable|string',
            'statut' => 'nullable|string|in:instance,paye,reporte,cour,impaye,devalide',
            'lignes' => 'nullable|array',
            'lignes.*.bon_achat_id' => 'required_with:lignes|exists:bon_achat_fournisseur,id',
            'lignes.*.montant_regle' => 'required_with:lignes|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $reglement->update([
                'date_reglement' => $validated['date_reglement'],
                'fournisseur_id' => $validated['fournisseur_id'],
                'type_reglement' => $validated['type_reglement'],
                'numero_piece' => $validated['numero_piece'] ?? null,
                'banque' => $validated['banque'] ?? null,
                'nom_beneficiaire' => $validated['nom_beneficiaire'] ?? null,
                'montant' => $validated['montant'],
                'date_encaissement' => $validated['date_encaissement'] ?? null,
                'observation' => $validated['observation'] ?? null,
                'statut' => $validated['statut'] ?? $reglement->statut,
            ]);

            // Supprimer les anciennes lignes et recréer
            $reglement->lignes()->delete();
            
            if (!empty($validated['lignes'])) {
                foreach ($validated['lignes'] as $ligne) {
                    ReglementFournisseurLigne::create([
                        'reglement_id' => $reglement->id,
                        'bon_achat_id' => $ligne['bon_achat_id'],
                        'montant_regle' => $ligne['montant_regle'],
                    ]);
                }
            }

            DB::commit();
            
            return response()->json($reglement->load(['fournisseur', 'lignes.bonAchat']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Marquer un règlement comme payé
     */
    public function markAsPaid($id)
    {
        $reglement = ReglementFournisseur::findOrFail($id);
        
        if ($reglement->statut === 'paye') {
            return response()->json(['message' => 'Ce règlement est déjà marqué comme payé'], 403);
        }

        $reglement->update(['statut' => 'paye']);
        
        return response()->json($reglement->load(['fournisseur', 'lignes.bonAchat']));
    }

    /**
     * Marquer un règlement comme reporté
     */
    public function markAsPostponed($id)
    {
        $reglement = ReglementFournisseur::findOrFail($id);
        
        if ($reglement->statut === 'paye') {
            return response()->json(['message' => 'Impossible de reporter un règlement déjà payé'], 403);
        }

        $reglement->update(['statut' => 'reporte']);
        
        return response()->json($reglement->load(['fournisseur', 'lignes.bonAchat']));
    }

    /**
     * Supprimer un règlement
     */
    public function destroy($id)
    {
        $reglement = ReglementFournisseur::findOrFail($id);
        
        if ($reglement->statut === 'paye') {
            return response()->json(['message' => 'Impossible de supprimer un règlement déjà payé'], 403);
        }

        $reglement->delete();
        
        return response()->json(['message' => 'Règlement supprimé avec succès']);
    }

    /**
     * Obtenir le prochain code règlement
     */
    public function nextCode()
    {
        $lastReglement = ReglementFournisseur::orderBy('id', 'desc')->first();
        $nextNumber = $lastReglement ? intval(substr($lastReglement->code_reglement, 3)) + 1 : 1;
        $codeReglement = 'RF-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        return response()->json(['code_reglement' => $codeReglement]);
    }

    /**
     * Obtenir les bons d'achat d'un fournisseur pour ventilation
     */
    public function getBonsAchatFournisseur($fournisseurId, Request $request)
    {
        // Get the reglement ID if we're editing an existing reglement
        $excludeReglementId = $request->query('exclude_reglement_id');
        
        // Récupérer les bons d'achat validés du fournisseur avec les montants déjà réglés
        $bonsAchat = BonAchatFournisseur::where('fournisseur_id', $fournisseurId)
            ->where('statut', 'valide')
            ->with(['fournisseur'])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($bon) use ($excludeReglementId) {
                // Calculer le montant déjà réglé pour ce bon (excluding current reglement if editing)
                $query = ReglementFournisseurLigne::where('bon_achat_id', $bon->id);
                
                if ($excludeReglementId) {
                    $query->where('reglement_id', '!=', $excludeReglementId);
                }
                
                $montantRegle = $query->sum('montant_regle');
                
                $bon->montant_regle = floatval($montantRegle);
                $bon->solde_restant = floatval($bon->total_ttc) - floatval($montantRegle);
                
                return $bon;
            });
        
        return response()->json($bonsAchat);
    }
}

