<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BonLivraisonClient;
use App\Models\ReglementClientLigne;
use App\Traits\UsesSelectedYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoriqueVentesController extends Controller
{
    use UsesSelectedYear;

    public function index(Request $request)
    {
        try {
            $selectedYear = $this->getSelectedYear();
            
            // Get all bon livraison with client information and payment details
            // Include all statuses (En attente, En cours, livre, annule)
            $ventes = BonLivraisonClient::with(['client'])
                ->whereYear('date', $selectedYear)
                ->orderBy('date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            $runningSoldeByClient = [];

            $ventes = $ventes->map(function ($bonLivraison) use (&$runningSoldeByClient) {
                $clientId = $bonLivraison->client_id;
                if (!isset($runningSoldeByClient[$clientId])) {
                    $runningSoldeByClient[$clientId] = 0;
                }

                // Calculate montant_paye from reglement_client_lignes
                $montantPaye = ReglementClientLigne::where('bon_livraison_id', $bonLivraison->id)
                    ->sum('montant_regle');
                
                $totalGeneral = (float) $bonLivraison->total_general;
                
                // Current bon solde
                $currentBonSolde = $totalGeneral - $montantPaye;
                
                // Running solde includes previous bons' solde
                $runningSoldeByClient[$clientId] += $currentBonSolde;
                
                return [
                    'id' => $bonLivraison->id,
                    'numero_bon' => $bonLivraison->numero_bon,
                    'date' => $bonLivraison->date?->format('Y-m-d'),
                    'client' => [
                        'id' => $bonLivraison->client?->id,
                        'code_client' => $bonLivraison->client?->code_client,
                        'raison_sociale' => $bonLivraison->client?->raison_sociale,
                    ],
                    'ville_livraison' => $bonLivraison->ville_livraison,
                    'total_quantites' => $bonLivraison->total_quantites,
                    'total_general' => $totalGeneral,
                    'montant_paye' => $montantPaye,
                    'solde' => $runningSoldeByClient[$clientId],
                    'reliquat' => max($montantPaye - $totalGeneral, 0),
                    'mode_paiement' => $bonLivraison->mode_paiement,
                    'statut' => $bonLivraison->statut,
                ];
            });
            
            // Sort back to descending for the view
            $ventes = $ventes->sortByDesc(function($bon) {
                return $bon['date'] . $bon['id'];
            })->values();

            return response()->json($ventes);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des données',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

