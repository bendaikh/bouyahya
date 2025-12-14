<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReglementClient;
use App\Models\ReglementClientLigne;
use App\Models\BonLivraisonClient;
use App\Models\CompteTresorerie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReglementClientController extends Controller
{
    /**
     * Liste tous les règlements clients
     */
    public function index()
    {
        $reglements = ReglementClient::with(['client', 'tresorerie', 'lignes.bonLivraison'])
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
            'client_id' => 'required|exists:clients,id',
            'type_reglement' => 'required|string',
            'numero_piece' => 'nullable|string',
            'banque' => 'nullable|string',
            'nom_tire' => 'nullable|string',
            'tresorerie_id' => 'nullable|exists:compte_tresoreries,id',
            'montant' => 'required|numeric|min:0',
            'date_encaissement' => 'nullable|date',
            'observation' => 'nullable|string',
            'statut' => 'nullable|string|in:instance,paye,reporte,cour,impaye,devalide',
            'etat_remboursement' => 'nullable|string|in:devalide,impaye',
            'lignes' => 'nullable|array',
            'lignes.*.bon_livraison_id' => 'required_with:lignes|exists:bon_livraison_clients,id',
            'lignes.*.montant_regle' => 'required_with:lignes|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Générer le code règlement
            $lastReglement = ReglementClient::orderBy('id', 'desc')->first();
            $nextNumber = $lastReglement ? intval(substr($lastReglement->code_reglement, 3)) + 1 : 1;
            $codeReglement = 'RC-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $reglement = ReglementClient::create([
                'code_reglement' => $codeReglement,
                'date_reglement' => $validated['date_reglement'],
                'client_id' => $validated['client_id'],
                'type_reglement' => $validated['type_reglement'],
                'numero_piece' => $validated['numero_piece'] ?? null,
                'banque' => $validated['banque'] ?? null,
                'nom_tire' => $validated['nom_tire'] ?? null,
                'tresorerie_id' => $validated['tresorerie_id'] ?? null,
                'montant' => $validated['montant'],
                'date_encaissement' => $validated['date_encaissement'] ?? null,
                'observation' => $validated['observation'] ?? null,
                'statut' => $validated['statut'] ?? 'impaye',
                'etat_remboursement' => $validated['etat_remboursement'] ?? null,
            ]);

            // Créer les lignes de ventilation
            if (!empty($validated['lignes'])) {
                foreach ($validated['lignes'] as $ligne) {
                    ReglementClientLigne::create([
                        'reglement_id' => $reglement->id,
                        'bon_livraison_id' => $ligne['bon_livraison_id'],
                        'montant_regle' => $ligne['montant_regle'],
                    ]);
                }
            }

            DB::commit();
            
            return response()->json($reglement->load(['client', 'tresorerie', 'lignes.bonLivraison']), 201);
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
        $reglement = ReglementClient::with(['client', 'tresorerie', 'lignes.bonLivraison'])
            ->findOrFail($id);
        
        // Add calculated fields for each bon_livraison in lignes
        $reglement->lignes->each(function ($ligne) use ($id) {
            if ($ligne->bonLivraison) {
                // Calculate total paid for this bon (excluding current reglement)
                $montantRegleAutres = ReglementClientLigne::where('bon_livraison_id', $ligne->bon_livraison_id)
                    ->join('reglements_clients', 'reglement_client_lignes.reglement_id', '=', 'reglements_clients.id')
                    ->whereIn('reglements_clients.statut', ['paye', 'cour', 'instance'])
                    ->where('reglement_client_lignes.reglement_id', '!=', $id)
                    ->sum('reglement_client_lignes.montant_regle');
                
                $ligne->bonLivraison->montant_regle = floatval($montantRegleAutres);
                $ligne->bonLivraison->solde_restant = floatval($ligne->bonLivraison->total_general) - floatval($montantRegleAutres);
            }
        });
        
        return response()->json($reglement);
    }

    /**
     * Mettre à jour un règlement
     */
    public function update(Request $request, $id)
    {
        $reglement = ReglementClient::findOrFail($id);

        $validated = $request->validate([
            'date_reglement' => 'required|date',
            'client_id' => 'required|exists:clients,id',
            'type_reglement' => 'required|string',
            'numero_piece' => 'nullable|string',
            'banque' => 'nullable|string',
            'nom_tire' => 'nullable|string',
            'tresorerie_id' => 'nullable|exists:compte_tresoreries,id',
            'montant' => 'required|numeric|min:0',
            'date_encaissement' => 'nullable|date',
            'observation' => 'nullable|string',
            'statut' => 'nullable|string|in:instance,paye,reporte,cour,impaye,devalide',
            'etat_remboursement' => 'nullable|string|in:devalide,impaye',
            'lignes' => 'nullable|array',
            'lignes.*.bon_livraison_id' => 'required_with:lignes|exists:bon_livraison_clients,id',
            'lignes.*.montant_regle' => 'required_with:lignes|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $reglement->update([
                'date_reglement' => $validated['date_reglement'],
                'client_id' => $validated['client_id'],
                'type_reglement' => $validated['type_reglement'],
                'numero_piece' => $validated['numero_piece'] ?? null,
                'banque' => $validated['banque'] ?? null,
                'nom_tire' => $validated['nom_tire'] ?? null,
                'tresorerie_id' => $validated['tresorerie_id'] ?? null,
                'montant' => $validated['montant'],
                'date_encaissement' => $validated['date_encaissement'] ?? null,
                'observation' => $validated['observation'] ?? null,
                'statut' => $validated['statut'] ?? $reglement->statut,
                'etat_remboursement' => $validated['etat_remboursement'] ?? $reglement->etat_remboursement,
            ]);

            // Supprimer les anciennes lignes et recréer
            $reglement->lignes()->delete();
            
            if (!empty($validated['lignes'])) {
                foreach ($validated['lignes'] as $ligne) {
                    ReglementClientLigne::create([
                        'reglement_id' => $reglement->id,
                        'bon_livraison_id' => $ligne['bon_livraison_id'],
                        'montant_regle' => $ligne['montant_regle'],
                    ]);
                }
            }

            DB::commit();
            
            return response()->json($reglement->load(['client', 'tresorerie', 'lignes.bonLivraison']));
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
        $reglement = ReglementClient::findOrFail($id);
        
        if ($reglement->statut === 'paye') {
            return response()->json(['message' => 'Ce règlement est déjà marqué comme payé'], 403);
        }

        $reglement->update(['statut' => 'paye']);
        
        return response()->json($reglement->load(['client', 'tresorerie', 'lignes.bonLivraison']));
    }

    /**
     * Supprimer un règlement
     */
    public function destroy($id)
    {
        $reglement = ReglementClient::findOrFail($id);
        
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
        $lastReglement = ReglementClient::orderBy('id', 'desc')->first();
        $nextNumber = $lastReglement ? intval(substr($lastReglement->code_reglement, 3)) + 1 : 1;
        $codeReglement = 'RC-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        return response()->json(['code_reglement' => $codeReglement]);
    }

    /**
     * Obtenir les bons de livraison d'un client pour ventilation
     */
    public function getBonsLivraisonClient($clientId, Request $request)
    {
        $excludeReglementId = $request->query('exclude_reglement_id');
        $etatRemboursement = $request->query('etat_remboursement');
        
        // Récupérer les bons de livraison livrés du client avec les montants déjà réglés
        $query = BonLivraisonClient::where('client_id', $clientId)
            ->where('statut', 'livre');
        
        $bonsLivraison = $query->with(['client'])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($bon) use ($excludeReglementId, $etatRemboursement) {
                // Calculer le montant déjà réglé pour ce bon
                $query = ReglementClientLigne::where('bon_livraison_id', $bon->id)
                    ->join('reglements_clients', 'reglement_client_lignes.reglement_id', '=', 'reglements_clients.id')
                    ->whereIn('reglements_clients.statut', ['paye', 'cour', 'instance']);
                
                if ($excludeReglementId) {
                    $query->where('reglement_client_lignes.reglement_id', '!=', $excludeReglementId);
                }
                
                $montantRegle = $query->sum('reglement_client_lignes.montant_regle');
                
                $bon->montant_regle = floatval($montantRegle);
                $bon->solde_restant = floatval($bon->total_general) - floatval($montantRegle);
                
                // If filtering by remboursement status
                if ($etatRemboursement) {
                    $isLinkedToStatut = ReglementClientLigne::join('reglements_clients', 'reglement_client_lignes.reglement_id', '=', 'reglements_clients.id')
                        ->where('reglement_client_lignes.bon_livraison_id', $bon->id)
                        ->where('reglements_clients.statut', $etatRemboursement)
                        ->when($excludeReglementId, function ($q) use ($excludeReglementId) {
                            $q->where('reglements_clients.id', '!=', $excludeReglementId);
                        })
                        ->exists();
                    
                    $bon->linked_to_remboursement_statut = $isLinkedToStatut;
                } else {
                    $bon->linked_to_remboursement_statut = false;
                }
                
                return $bon;
            });
        
        return response()->json($bonsLivraison);
    }

    /**
     * Obtenir les comptes de trésorerie disponibles
     */
    public function getTresoreries()
    {
        $comptes = CompteTresorerie::orderBy('libelle')->get();
        return response()->json($comptes);
    }
}

