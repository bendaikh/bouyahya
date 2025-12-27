<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BonAchatArticle;
use App\Models\BonLivraisonClientArticle;
use App\Models\BonAchatFournisseur;
use App\Models\BonLivraisonClient;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display the stock list with calculated quantities
     * Stock = Purchased (from Bon d'achat Fournisseur) - Sold (from Bon de livraison)
     */
    public function index()
    {
        // Get all purchased quantities grouped by article reference
        // Only count from validated bon d'achat (statut = 'valide')
        $purchased = BonAchatArticle::join('bon_achat_fournisseur', 'bon_achat_articles.bon_achat_id', '=', 'bon_achat_fournisseur.id')
            ->where('bon_achat_fournisseur.statut', 'valide')
            ->select('bon_achat_articles.ref_article', DB::raw('SUM(bon_achat_articles.qte) as total_purchased'))
            ->groupBy('bon_achat_articles.ref_article')
            ->get()
            ->keyBy('ref_article');

        // Get the last purchase price for each article
        $purchasePrices = BonAchatArticle::join('bon_achat_fournisseur', 'bon_achat_articles.bon_achat_id', '=', 'bon_achat_fournisseur.id')
            ->where('bon_achat_fournisseur.statut', 'valide')
            ->select('bon_achat_articles.ref_article', 'bon_achat_articles.prix_unitaire_ttc')
            ->orderBy('bon_achat_articles.id', 'desc')
            ->get()
            ->unique('ref_article')
            ->keyBy('ref_article');

        // Get all sold quantities grouped by article code
        // Only count from delivered bon de livraison (statut = 'Livré')
        $sold = BonLivraisonClientArticle::join('bon_livraison_clients', 'bon_livraison_client_articles.bon_livraison_client_id', '=', 'bon_livraison_clients.id')
            ->where('bon_livraison_clients.statut', 'Livré')
            ->select('bon_livraison_client_articles.code_article', DB::raw('SUM(bon_livraison_client_articles.quantite) as total_sold'))
            ->groupBy('bon_livraison_client_articles.code_article')
            ->get()
            ->keyBy('code_article');

        // Get the last sale price for each article
        $salePrices = BonLivraisonClientArticle::join('bon_livraison_clients', 'bon_livraison_client_articles.bon_livraison_client_id', '=', 'bon_livraison_clients.id')
            ->where('bon_livraison_clients.statut', 'Livré')
            ->select('bon_livraison_client_articles.code_article', 'bon_livraison_client_articles.prix_unitaire')
            ->orderBy('bon_livraison_client_articles.id', 'desc')
            ->get()
            ->unique('code_article')
            ->keyBy('code_article');

        // Get all articles from database
        $articles = Article::all();

        // Get famille settings for display
        $famillesJson = Setting::getValue('familles_article', '[]');
        $familles = json_decode($famillesJson, true) ?: [];
        $famillesMap = collect($familles)->keyBy('id');

        // Get unités de mesure for display
        $unitesJson = Setting::getValue('unites_mesure', '[]');
        $unites = json_decode($unitesJson, true) ?: [];
        $unitesMap = collect($unites)->keyBy('id');

        // Calculate stock for each article
        $stockData = [];
        $totalStock = 0;
        $lowStockCount = 0;
        $inStockCount = 0;

        foreach ($articles as $article) {
            $ref = $article->reference;
            
            // Get purchased and sold quantities
            $purchasedQty = $purchased->has($ref) ? $purchased[$ref]->total_purchased : 0;
            $soldQty = $sold->has($ref) ? $sold[$ref]->total_sold : 0;
            
            // Calculate current stock
            $currentStock = $purchasedQty - $soldQty;
            
            // Get prices - prefer transaction prices, fallback to article prices
            $prixAchat = 0;
            $prixVente = 0;
            
            // Get purchase price from last bon d'achat, or from article
            if ($purchasePrices->has($ref)) {
                $prixAchat = floatval($purchasePrices[$ref]->prix_unitaire_ttc);
            } elseif ($article->prix_achat) {
                $prixAchat = floatval($article->prix_achat);
            }
            
            // Get sale price from last bon de livraison, or from article
            if ($salePrices->has($ref)) {
                $prixVente = floatval($salePrices[$ref]->prix_unitaire);
            } elseif ($article->prix_vente) {
                $prixVente = floatval($article->prix_vente);
            }
            
            // Determine status
            $stockMinimum = $article->stock_minimum ?? 0;
            if ($currentStock <= 0) {
                $status = 'Rupture';
                $statusClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            } elseif ($currentStock <= $stockMinimum) {
                $status = 'Stock faible';
                $statusClass = 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
                $lowStockCount++;
            } else {
                $status = 'Normal';
                $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                $inStockCount++;
            }

            // Get famille name
            $familleNom = '';
            $familleIdStr = '';
            if ($article->famille_id) {
                $familleIdStr = strval($article->famille_id);
                if (isset($famillesMap[$article->famille_id])) {
                    $familleNom = $famillesMap[$article->famille_id]['nom'];
                }
            }

            // Get unite mesure
            $uniteNom = '';
            $uniteSymbole = '';
            if ($article->unite_mesure_id && isset($unitesMap[$article->unite_mesure_id])) {
                $uniteNom = $unitesMap[$article->unite_mesure_id]['nom'];
                $uniteSymbole = $unitesMap[$article->unite_mesure_id]['symbole'] ?? '';
            }

            $stockData[] = [
                'id' => $article->id,
                'reference' => $article->reference,
                'designation' => $article->designation,
                'famille' => $familleNom,
                'famille_id' => $familleIdStr,
                'unite_mesure' => $uniteNom,
                'unite_symbole' => $uniteSymbole,
                'prix_achat' => $prixAchat,
                'prix_vente' => $prixVente,
                'stock_actuel' => $currentStock,
                'stock_minimum' => $stockMinimum,
                'quantite_achetee' => $purchasedQty,
                'quantite_vendue' => $soldQty,
                'status' => $status,
                'status_class' => $statusClass,
                'actif' => $article->actif,
            ];

            $totalStock += $currentStock;
        }

        // Get all familles for filter dropdown - ensure IDs are strings
        $famillesList = collect($familles)->map(function ($f) {
            return ['id' => strval($f['id']), 'nom' => $f['nom']];
        })->values()->all();

        return view('stock.stocks', [
            'page_title' => 'Les stocks',
            'stocks' => $stockData,
            'familles' => $famillesList,
            'totalArticles' => count($articles),
            'totalStock' => $totalStock,
            'inStockCount' => $inStockCount,
            'lowStockCount' => $lowStockCount,
        ]);
    }

    /**
     * API endpoint for stock data (for Vue components if needed)
     */
    public function getStockData(Request $request)
    {
        // Same logic as index but returns JSON
        $purchased = BonAchatArticle::join('bon_achat_fournisseur', 'bon_achat_articles.bon_achat_id', '=', 'bon_achat_fournisseur.id')
            ->where('bon_achat_fournisseur.statut', 'valide')
            ->select('bon_achat_articles.ref_article', DB::raw('SUM(bon_achat_articles.qte) as total_purchased'))
            ->groupBy('bon_achat_articles.ref_article')
            ->get()
            ->keyBy('ref_article');

        $purchasePrices = BonAchatArticle::join('bon_achat_fournisseur', 'bon_achat_articles.bon_achat_id', '=', 'bon_achat_fournisseur.id')
            ->where('bon_achat_fournisseur.statut', 'valide')
            ->select('bon_achat_articles.ref_article', 'bon_achat_articles.prix_unitaire_ttc')
            ->orderBy('bon_achat_articles.id', 'desc')
            ->get()
            ->unique('ref_article')
            ->keyBy('ref_article');

        $sold = BonLivraisonClientArticle::join('bon_livraison_clients', 'bon_livraison_client_articles.bon_livraison_client_id', '=', 'bon_livraison_clients.id')
            ->where('bon_livraison_clients.statut', 'Livré')
            ->select('bon_livraison_client_articles.code_article', DB::raw('SUM(bon_livraison_client_articles.quantite) as total_sold'))
            ->groupBy('bon_livraison_client_articles.code_article')
            ->get()
            ->keyBy('code_article');

        $salePrices = BonLivraisonClientArticle::join('bon_livraison_clients', 'bon_livraison_client_articles.bon_livraison_client_id', '=', 'bon_livraison_clients.id')
            ->where('bon_livraison_clients.statut', 'Livré')
            ->select('bon_livraison_client_articles.code_article', 'bon_livraison_client_articles.prix_unitaire')
            ->orderBy('bon_livraison_client_articles.id', 'desc')
            ->get()
            ->unique('code_article')
            ->keyBy('code_article');

        $articles = Article::all();

        $famillesJson = Setting::getValue('familles_article', '[]');
        $familles = json_decode($famillesJson, true) ?: [];
        $famillesMap = collect($familles)->keyBy('id');

        $unitesJson = Setting::getValue('unites_mesure', '[]');
        $unites = json_decode($unitesJson, true) ?: [];
        $unitesMap = collect($unites)->keyBy('id');

        $stockData = [];

        foreach ($articles as $article) {
            $ref = $article->reference;
            $purchasedQty = $purchased->has($ref) ? $purchased[$ref]->total_purchased : 0;
            $soldQty = $sold->has($ref) ? $sold[$ref]->total_sold : 0;
            $currentStock = $purchasedQty - $soldQty;
            
            $prixAchat = 0;
            $prixVente = 0;
            
            if ($purchasePrices->has($ref)) {
                $prixAchat = floatval($purchasePrices[$ref]->prix_unitaire_ttc);
            } elseif ($article->prix_achat) {
                $prixAchat = floatval($article->prix_achat);
            }
            
            if ($salePrices->has($ref)) {
                $prixVente = floatval($salePrices[$ref]->prix_unitaire);
            } elseif ($article->prix_vente) {
                $prixVente = floatval($article->prix_vente);
            }
            
            $stockMinimum = $article->stock_minimum ?? 0;
            if ($currentStock <= 0) {
                $status = 'Rupture';
            } elseif ($currentStock <= $stockMinimum) {
                $status = 'Stock faible';
            } else {
                $status = 'Normal';
            }

            $familleNom = '';
            if ($article->famille_id && isset($famillesMap[$article->famille_id])) {
                $familleNom = $famillesMap[$article->famille_id]['nom'];
            }

            $uniteNom = '';
            $uniteSymbole = '';
            if ($article->unite_mesure_id && isset($unitesMap[$article->unite_mesure_id])) {
                $uniteNom = $unitesMap[$article->unite_mesure_id]['nom'];
                $uniteSymbole = $unitesMap[$article->unite_mesure_id]['symbole'] ?? '';
            }

            $stockData[] = [
                'id' => $article->id,
                'reference' => $article->reference,
                'designation' => $article->designation,
                'famille' => $familleNom,
                'unite_mesure' => $uniteNom,
                'unite_symbole' => $uniteSymbole,
                'prix_achat' => $prixAchat,
                'prix_vente' => $prixVente,
                'stock_actuel' => $currentStock,
                'stock_minimum' => $stockMinimum,
                'quantite_achetee' => $purchasedQty,
                'quantite_vendue' => $soldQty,
                'status' => $status,
            ];
        }

        return response()->json([
            'stocks' => $stockData,
            'familles' => collect($familles)->map(fn($f) => ['id' => strval($f['id']), 'nom' => $f['nom']])->values()->all(),
        ]);
    }
}
