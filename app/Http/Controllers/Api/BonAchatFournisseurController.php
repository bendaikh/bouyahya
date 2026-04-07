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
     * Uses the same logic as ReleveCompteFournisseurs:
     * - Credit = total_ttc of bons
     * - Debit = montant of reglements (paye, cour, instance status only)
     * - Solde = cumulative (Credit - Debit) per fournisseur/client group
     */
    public function historique(Request $request)
    {
        $selectedYear = $this->getSelectedYear();
        
        // Get all valid bons for the selected year
        $bons = BonAchatFournisseur::with(['fournisseur', 'articles'])
            ->whereYear('date', $selectedYear)
            ->where('statut', 'valide')
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Get all reglements for the same year that are linked to these bons
        $reglements = ReglementFournisseur::with(['lignes.bonAchat'])
            ->whereIn('statut', ['paye', 'cour', 'instance'])
            ->whereHas('lignes.bonAchat', function ($query) use ($selectedYear) {
                $query->whereYear('date', $selectedYear)
                      ->where('statut', 'valide');
            })
            ->orderBy('date_reglement', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Build a combined timeline of events (bons and reglements)
        // Each bon adds to the balance (Credit), each reglement reduces it (Debit)
        $events = [];
        
        foreach ($bons as $bon) {
            $clientLivre = trim($bon->client_livre ?? 'default');
            $events[] = [
                'type' => 'bon',
                'date' => $bon->date->format('Y-m-d'),
                'id' => $bon->id,
                'fournisseur_id' => $bon->fournisseur_id,
                'client_livre' => $clientLivre,
                'amount' => floatval($bon->total_ttc),
                'bon' => $bon,
            ];
        }
        
        foreach ($reglements as $reglement) {
            // Get linked bons to determine which client_livre groups this payment affects
            foreach ($reglement->lignes as $ligne) {
                if ($ligne->bonAchat) {
                    $clientLivre = trim($ligne->bonAchat->client_livre ?? 'default');
                    $events[] = [
                        'type' => 'reglement',
                        'date' => $reglement->date_reglement,
                        'id' => $reglement->id,
                        'fournisseur_id' => $reglement->fournisseur_id,
                        'client_livre' => $clientLivre,
                        'amount' => floatval($reglement->montant),
                        'reglement' => $reglement,
                        'bon_id' => $ligne->bon_achat_id,
                    ];
                    break; // Only add one event per reglement (the full amount)
                }
            }
        }
        
        // Sort events by date, then by type (bons first), then by id
        usort($events, function ($a, $b) {
            $dateCompare = strcmp($a['date'], $b['date']);
            if ($dateCompare !== 0) return $dateCompare;
            // Bons come before reglements on the same date
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'bon' ? -1 : 1;
            }
            return $a['id'] - $b['id'];
        });
        
        // Calculate running balance per fournisseur/client group
        $runningBalanceByGroup = [];
        $bonPayments = []; // Track total payments per bon
        
        foreach ($events as $event) {
            $groupId = $event['fournisseur_id'] . '_' . $event['client_livre'];
            
            if (!isset($runningBalanceByGroup[$groupId])) {
                $runningBalanceByGroup[$groupId] = 0;
            }
            
            if ($event['type'] === 'bon') {
                // Bon adds to balance (Credit = what we owe)
                $runningBalanceByGroup[$groupId] += $event['amount'];
                
                // Store the running balance at this point for this bon
                $event['bon']->running_balance = round($runningBalanceByGroup[$groupId], 2);
            } else {
                // Reglement reduces balance (Debit = what we paid)
                $runningBalanceByGroup[$groupId] -= $event['amount'];
                
                // Track payments per bon
                if (isset($event['bon_id'])) {
                    if (!isset($bonPayments[$event['bon_id']])) {
                        $bonPayments[$event['bon_id']] = 0;
                    }
                    $bonPayments[$event['bon_id']] += $event['amount'];
                }
            }
        }
        
        // Get the final balance for each group (after all events)
        $finalBalanceByGroup = $runningBalanceByGroup;
        
        // Now calculate solde for each bon based on final balance
        // We need to recalculate from scratch to get proper cumulative solde per bon
        $runningBalanceByGroup = [];
        
        $bons = $bons->map(function ($bon) use (&$runningBalanceByGroup, $bonPayments) {
            $clientLivre = trim($bon->client_livre ?? 'default');
            $groupId = $bon->fournisseur_id . '_' . $clientLivre;
            
            if (!isset($runningBalanceByGroup[$groupId])) {
                $runningBalanceByGroup[$groupId] = 0;
            }
            
            $ttc = floatval($bon->total_ttc);
            $paye = isset($bonPayments[$bon->id]) ? $bonPayments[$bon->id] : 0;
            
            // Add this bon's net to the running balance
            $runningBalanceByGroup[$groupId] += ($ttc - $paye);
            
            $currentBalance = round($runningBalanceByGroup[$groupId], 2);
            
            $bon->montant_paye = $paye;
            
            if ($currentBalance > 0) {
                $bon->solde = $currentBalance;
                $bon->reliquat = 0;
            } elseif ($currentBalance < 0) {
                $bon->solde = 0;
                $bon->reliquat = abs($currentBalance);
            } else {
                $bon->solde = 0;
                $bon->reliquat = 0;
            }
            
            return $bon;
        });

        // Sort back to descending for the view
        $bons = $bons->sortByDesc(function($bon) {
            return $bon->date->format('Y-m-d') . str_pad($bon->id, 10, '0', STR_PAD_LEFT);
        })->values();
        
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
