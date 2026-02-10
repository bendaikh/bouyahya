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
    public function index(Request $request)
    {
        // Get selected year from session, default to current year
        $currentYear = Carbon::now()->year;
        $selectedYear = session('selected_year', $currentYear);
        $currentMonth = Carbon::now()->month;
        
        // Generate list of available years (from 2020 to current year + 1)
        $availableYears = range(2020, $currentYear + 1);
        
        // Check if user is commercial
        $isCommercial = auth()->user()->hasRole('commercial');
        
        // Date range for selected year
        $yearStart = Carbon::create($selectedYear, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($selectedYear, 12, 31)->endOfYear();
        
        // Total Ventes for selected year
        $totalVentes = BonLivraisonClient::whereYear('date', $selectedYear)->sum('total_general') ?? 0;
        
        // Total Achats for selected year
        $totalAchats = BonAchatFournisseur::whereYear('date', $selectedYear)->sum('total_ttc') ?? 0;
        
        // Solde Clients for selected year
        $totalBonLivraisonClients = BonLivraisonClient::whereYear('date', $selectedYear)->sum('total_general') ?? 0;
        $totalPaidByClients = ReglementClient::whereYear('date_reglement', $selectedYear)
            ->whereIn('statut', ['paye', 'cour', 'instance'])->sum('montant') ?? 0;
        $soldeClients = $totalBonLivraisonClients - $totalPaidByClients;
        
        // Solde Fournisseurs for selected year
        $totalBonAchatFournisseurs = BonAchatFournisseur::whereYear('date', $selectedYear)->sum('total_ttc') ?? 0;
        $totalPaidToFournisseurs = ReglementFournisseur::whereYear('date_reglement', $selectedYear)
            ->whereIn('statut', ['paye', 'cour', 'instance'])->sum('montant') ?? 0;
        $soldeFournisseurs = $totalBonAchatFournisseurs - $totalPaidToFournisseurs;
        
        // 5 Derniers Bons de Livraisons for selected year
        $derniersBonsLivraison = BonLivraisonClient::with('client')
            ->whereYear('date', $selectedYear)
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
        
        // 5 Derniers Bons d'Achats for selected year
        $derniersBonsAchat = BonAchatFournisseur::with('fournisseur')
            ->whereYear('date', $selectedYear)
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
        
        // Monthly data for chart (Achats and Ventes per month) for selected year
        $mouvementsData = $this->getMonthlyMovements($selectedYear);
        
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
        
        // Payment methods breakdown for pie chart (for selected year)
        $paymentBreakdown = $this->getPaymentBreakdown($selectedYear);
        
        // Encaissements and Decaissements totals for selected year
        $encaissements = ReglementClient::whereYear('date_reglement', $selectedYear)
            ->whereIn('statut', ['paye', 'cour', 'instance'])->sum('montant') ?? 0;
        $decaissements = ReglementFournisseur::whereYear('date_reglement', $selectedYear)
            ->whereIn('statut', ['paye', 'cour', 'instance'])->sum('montant') ?? 0;
        
        // Charges total for selected year
        $charges = \App\Models\ChargeEntry::whereYear('date', $selectedYear)->sum('montant') ?? 0;
        
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
            'currentYear',
            'selectedYear',
            'availableYears',
            'isCommercial'
        ));
    }
    
    private function getMonthlyMovements($year)
    {
        $months = [];
        $achatsData = [];
        $ventesData = [];
        $chargesData = [];
        
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
            $charges = \App\Models\ChargeEntry::whereBetween('date', [$startDate, $endDate])->sum('montant') ?? 0;
            
            $months[] = $monthNames[$month];
            $achatsData[] = floatval($achats);
            $ventesData[] = floatval($ventes);
            $chargesData[] = floatval($charges);
        }
        
        return [
            'labels' => $months,
            'achats' => $achatsData,
            'ventes' => $ventesData,
            'charges' => $chargesData,
        ];
    }
    
    private function getPaymentBreakdown($year = null)
    {
        // Get breakdown by payment type (type_reglement)
        $query = ReglementClient::selectRaw('type_reglement, SUM(montant) as total')
            ->whereIn('statut', ['paye', 'cour', 'instance']);
        
        if ($year) {
            $query->whereYear('date_reglement', $year);
        }
        
        $breakdown = $query->groupBy('type_reglement')->get();
        
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
