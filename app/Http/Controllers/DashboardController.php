<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BonLivraisonClient;
use App\Models\BonAchatFournisseur;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\CompteTresorerie;
use App\Models\ReglementClient;
use App\Models\ReglementFournisseur;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        
        // Total Ventes (sum of all bon livraison totals)
        $totalVentes = BonLivraisonClient::sum('total_general') ?? 0;
        
        // Total Achats (sum of all bon achat totals)
        $totalAchats = BonAchatFournisseur::sum('total_ttc') ?? 0;
        
        // Solde Clients (unpaid client balance)
        // Calculate total bon livraison - total paid
        $totalBonLivraisonClients = BonLivraisonClient::sum('total_general') ?? 0;
        $totalPaidByClients = ReglementClient::whereIn('statut', ['paye', 'cour', 'instance'])->sum('montant') ?? 0;
        $soldeClients = $totalBonLivraisonClients - $totalPaidByClients;
        
        // Solde Fournisseurs (unpaid supplier balance)
        $totalBonAchatFournisseurs = BonAchatFournisseur::sum('total_ttc') ?? 0;
        $totalPaidToFournisseurs = ReglementFournisseur::whereIn('statut', ['paye', 'cour', 'instance'])->sum('montant') ?? 0;
        $soldeFournisseurs = $totalBonAchatFournisseurs - $totalPaidToFournisseurs;
        
        // 5 Derniers Bons de Livraisons
        $derniersBonsLivraison = BonLivraisonClient::with('client')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($bon) {
                return [
                    'date' => Carbon::parse($bon->date)->format('d-m-Y'),
                    'numero' => $bon->numero_bon,
                    'client' => $bon->client->raison_sociale ?? 'N/A',
                    'montant_ttc' => number_format($bon->total_general, 2, '.', ''),
                    'statut' => $this->getStatutLabel($bon->statut),
                    'statut_class' => $this->getStatutClass($bon->statut),
                ];
            });
        
        // 5 Derniers Bons d'Achats
        $derniersBonsAchat = BonAchatFournisseur::with('fournisseur')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($bon) {
                return [
                    'date' => Carbon::parse($bon->date)->format('d-m-Y'),
                    'numero' => $bon->numero_bon,
                    'fournisseur' => $bon->fournisseur->nom_fournisseur ?? 'N/A',
                    'montant_ttc' => number_format($bon->total_ttc, 2, '.', ''),
                    'statut' => $this->getStatutLabel($bon->statut),
                    'statut_class' => $this->getStatutClass($bon->statut),
                ];
            });
        
        // Monthly data for chart (Achats and Ventes per month)
        $mouvementsData = $this->getMonthlyMovements($currentYear);
        
        // Etat Caisse (Tresorerie accounts of type 'caisse')
        $etatCaisse = CompteTresorerie::where('type_compte', 'caisse')
            ->orderBy('libelle')
            ->get()
            ->map(function ($compte) {
                return [
                    'libelle' => $compte->libelle,
                    'solde' => $compte->solde_actuel ?? $compte->solde_initial ?? 0,
                ];
            });
        
        // Etat Banque (Tresorerie accounts of type 'banque')
        $etatBanque = CompteTresorerie::where('type_compte', 'banque')
            ->orderBy('libelle')
            ->get()
            ->map(function ($compte) {
                return [
                    'libelle' => $compte->libelle,
                    'solde' => $compte->solde_actuel ?? $compte->solde_initial ?? 0,
                ];
            });
        
        // Payment methods breakdown for pie chart
        $paymentBreakdown = $this->getPaymentBreakdown();
        
        // Encaissements and Decaissements totals
        $encaissements = ReglementClient::whereIn('statut', ['paye', 'cour', 'instance'])->sum('montant') ?? 0;
        $decaissements = ReglementFournisseur::whereIn('statut', ['paye', 'cour', 'instance'])->sum('montant') ?? 0;
        
        // Charges total
        $charges = \App\Models\ChargeEntry::sum('montant') ?? 0;
        
        return view('dashboard', compact(
            'totalVentes',
            'totalAchats',
            'soldeClients',
            'soldeFournisseurs',
            'derniersBonsLivraison',
            'derniersBonsAchat',
            'mouvementsData',
            'etatCaisse',
            'etatBanque',
            'paymentBreakdown',
            'encaissements',
            'decaissements',
            'charges',
            'currentYear'
        ));
    }
    
    private function getMonthlyMovements($year)
    {
        $months = [];
        $achatsData = [];
        $ventesData = [];
        
        $monthNames = [
            1 => 'JANVIER', 2 => 'FÉV', 3 => 'MARS', 4 => 'AVRIL', 
            5 => 'MAI', 6 => 'JUIN', 7 => 'JUILLET', 8 => 'AOÛT', 
            9 => 'SEPT', 10 => 'OCT', 11 => 'NOV', 12 => 'DÉC'
        ];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            
            $achats = BonAchatFournisseur::whereBetween('date', [$startDate, $endDate])->sum('total_ttc') ?? 0;
            $ventes = BonLivraisonClient::whereBetween('date', [$startDate, $endDate])->sum('total_general') ?? 0;
            
            $months[] = $monthNames[$month];
            $achatsData[] = floatval($achats);
            $ventesData[] = floatval($ventes);
        }
        
        return [
            'labels' => $months,
            'achats' => $achatsData,
            'ventes' => $ventesData,
        ];
    }
    
    private function getPaymentBreakdown()
    {
        // Get breakdown by payment type (type_reglement)
        $breakdown = ReglementClient::selectRaw('type_reglement, SUM(montant) as total')
            ->whereIn('statut', ['paye', 'cour', 'instance'])
            ->groupBy('type_reglement')
            ->get();
        
        $totalPayments = $breakdown->sum('total');
        
        $result = [
            'cheques' => 0,
            'especes' => 0,
            'traites' => 0,
            'virements' => 0,
        ];
        
        foreach ($breakdown as $item) {
            $type = strtolower($item->type_reglement ?? '');
            if (str_contains($type, 'cheque') || str_contains($type, 'chèque')) {
                $result['cheques'] += $item->total;
            } elseif (str_contains($type, 'espece') || str_contains($type, 'espèce') || str_contains($type, 'cash')) {
                $result['especes'] += $item->total;
            } elseif (str_contains($type, 'traite')) {
                $result['traites'] += $item->total;
            } elseif (str_contains($type, 'virement')) {
                $result['virements'] += $item->total;
            }
        }
        
        // Convert to percentages
        if ($totalPayments > 0) {
            foreach ($result as $key => $value) {
                $result[$key] = round(($value / $totalPayments) * 100, 0);
            }
        }
        
        return $result;
    }
    
    private function getStatutLabel($statut)
    {
        $labels = [
            'brouillon' => 'Brouillon',
            'valide' => 'Validé',
            'livre' => 'Livré',
            'annule' => 'Annulé',
            'paye' => 'Payé',
            'impaye' => 'Impayé',
            'encours' => 'En cours',
        ];
        
        return $labels[$statut] ?? ucfirst($statut ?? 'N/A');
    }
    
    private function getStatutClass($statut)
    {
        $classes = [
            'brouillon' => 'bg-gray-100 text-gray-700',
            'valide' => 'bg-blue-100 text-blue-700',
            'livre' => 'bg-green-100 text-green-700',
            'annule' => 'bg-red-100 text-red-700',
            'paye' => 'bg-green-100 text-green-700',
            'impaye' => 'bg-red-100 text-red-700',
            'encours' => 'bg-yellow-100 text-yellow-700',
        ];
        
        return $classes[$statut] ?? 'bg-gray-100 text-gray-700';
    }
}
