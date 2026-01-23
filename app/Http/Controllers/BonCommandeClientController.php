<?php

namespace App\Http\Controllers;

use App\Models\BonCommandeClient;
use App\Models\BonLivraisonClient;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Article;
use App\Models\BonAchatArticle;
use App\Models\BonLivraisonClientArticle;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonCommandeClientController extends Controller
{
    public function index()
    {
        $bonCommandes = BonCommandeClient::with(['client', 'fournisseur'])->orderBy('created_at', 'desc')->get();
        $clients = Client::orderBy('raison_sociale')->get();
        $fournisseurs = Fournisseur::orderBy('nom_fournisseur')->get();
        
        // Calculate stock for each article as in StockController
        $purchased = BonAchatArticle::join('bon_achat_fournisseur', 'bon_achat_articles.bon_achat_id', '=', 'bon_achat_fournisseur.id')
            ->where('bon_achat_fournisseur.statut', 'valide')
            ->select('bon_achat_articles.ref_article', DB::raw('SUM(bon_achat_articles.qte) as total_purchased'))
            ->groupBy('bon_achat_articles.ref_article')
            ->get()
            ->keyBy('ref_article');

        $sold = BonLivraisonClientArticle::join('bon_livraison_clients', 'bon_livraison_client_articles.bon_livraison_client_id', '=', 'bon_livraison_clients.id')
            ->where('bon_livraison_clients.statut', 'Livré')
            ->select('bon_livraison_client_articles.code_article', DB::raw('SUM(bon_livraison_client_articles.quantite) as total_sold'))
            ->groupBy('bon_livraison_client_articles.code_article')
            ->get()
            ->keyBy('code_article');

        $articles = Article::where('actif', true)->orderBy('designation')->get()->map(function($article) use ($purchased, $sold) {
            $ref = $article->reference;
            $purchasedQty = $purchased->has($ref) ? $purchased[$ref]->total_purchased : 0;
            $soldQty = $sold->has($ref) ? $sold[$ref]->total_sold : 0;
            $article->stock_actuel = $purchasedQty - $soldQty;
            return $article;
        });
        
        // Get cities from settings
        $cities = json_decode(Setting::getValue('cities', '[]'), true);
        
        return view('ventes.bon-commande', [
            'page_title' => 'Bon de commande',
            'bonCommandes' => $bonCommandes,
            'clients' => $clients,
            'fournisseurs' => $fournisseurs,
            'articles' => $articles,
            'cities' => $cities,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'numero_bon' => 'required|string|unique:bon_commande_clients,numero_bon',
            'client_id' => 'required|exists:clients,id',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'mode_paiement' => 'required|string',
            'echeance' => 'nullable|date',
            'ville_livraison' => 'nullable|string',
            'motif_annulation' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.code_article' => 'required|string',
            'items.*.designation' => 'required|string',
            'items.*.quantite' => 'required|numeric|min:1',
            'items.*.prix_unitaire' => 'required|numeric|min:0',
            'items.*.sous_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $totalQuantites = array_sum(array_column($request->items, 'quantite'));
            $totalGeneral = array_sum(array_column($request->items, 'sous_total'));

            $bonCommande = BonCommandeClient::create([
                'numero_bon' => $request->numero_bon,
                'date' => $request->date,
                'client_id' => $request->client_id,
                'fournisseur_id' => $request->fournisseur_id,
                'mode_paiement' => $request->mode_paiement,
                'echeance' => $request->echeance,
                'ville_livraison' => $request->ville_livraison,
                'total_quantites' => $totalQuantites,
                'total_general' => $totalGeneral,
                'statut' => 'En attente',
                'motif_annulation' => $request->motif_annulation,
            ]);

            foreach ($request->items as $item) {
                $bonCommande->articles()->create([
                    'code_article' => $item['code_article'],
                    'designation' => $item['designation'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'sous_total' => $item['sous_total'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Bon de commande créé avec succès',
                'bonCommande' => $bonCommande->load('client', 'articles'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la création du bon de commande: ' . $e->getMessage()], 500);
        }
    }

    public function nextNumero()
    {
        $year = date('Y');
        $lastBon = BonCommandeClient::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastBon) {
            return response()->json(['numero' => 'BC-' . $year . '/0001']);
        }

        preg_match('/BC-\d{4}\/(\d+)/', $lastBon->numero_bon, $matches);
        $lastNumber = $matches[1] ?? 0;
        $nextNumber = (int)$lastNumber + 1;

        return response()->json(['numero' => 'BC-' . $year . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT)]);
    }

    public function show($id)
    {
        $bonCommande = BonCommandeClient::with(['articles', 'client', 'fournisseur'])->findOrFail($id);
        return response()->json($bonCommande);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'numero_bon' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'mode_paiement' => 'required|string',
            'echeance' => 'nullable|date',
            'ville_livraison' => 'nullable|string',
            'motif_annulation' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.code_article' => 'required|string',
            'items.*.designation' => 'required|string',
            'items.*.quantite' => 'required|numeric|min:1',
            'items.*.prix_unitaire' => 'required|numeric|min:0',
            'items.*.sous_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $bonCommande = BonCommandeClient::findOrFail($id);

            $totalQuantites = array_sum(array_column($request->items, 'quantite'));
            $totalGeneral = array_sum(array_column($request->items, 'sous_total'));

            $bonCommande->update([
                'date' => $request->date,
                'numero_bon' => $request->numero_bon,
                'client_id' => $request->client_id,
                'fournisseur_id' => $request->fournisseur_id,
                'mode_paiement' => $request->mode_paiement,
                'echeance' => $request->echeance,
                'ville_livraison' => $request->ville_livraison,
                'total_quantites' => $totalQuantites,
                'total_general' => $totalGeneral,
                'motif_annulation' => $request->motif_annulation,
            ]);

            // Delete old articles and create new ones
            $bonCommande->articles()->delete();
            
            foreach ($request->items as $item) {
                $bonCommande->articles()->create([
                    'code_article' => $item['code_article'],
                    'designation' => $item['designation'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'sous_total' => $item['sous_total'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Bon de commande modifié avec succès',
                'bonCommande' => $bonCommande->load('client', 'articles'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la modification du bon de commande: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $bonCommande = BonCommandeClient::findOrFail($id);
            $bonCommande->delete();
            
            return response()->json(['message' => 'Bon de commande supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
        }
    }

    public function print($id)
    {
        $bonCommande = BonCommandeClient::with(['client', 'articles'])->findOrFail($id);
        
        return view('ventes.bon-commande-print', [
            'bonCommande' => $bonCommande,
        ]);
    }

    public function printCharge(Request $request)
    {
        $request->validate([
            'bon_commande_ids' => 'required|array|min:1',
            'bon_commande_ids.*' => 'exists:bon_commande_clients,id',
        ]);

        $bonCommandes = BonCommandeClient::with(['client', 'articles'])
            ->whereIn('id', $request->bon_commande_ids)
            ->get();

        if ($bonCommandes->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun bon de commande trouvé');
        }

        // Group articles by designation (product name) - this creates sections like "FIL D'ATTACHE JP08"
        $groupedItems = [];
        foreach ($bonCommandes as $bonCommande) {
            foreach ($bonCommande->articles as $article) {
                // Use designation as the key to group by product type
                $key = $article->designation;
                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'reference' => $article->code_article, // Reference like F/A, P06, P08
                        'designation' => $article->designation, // Product name like "FIL D'ATTACHE JP08"
                        'items' => []
                    ];
                }
                
                // Add item with client and ville info
                $groupedItems[$key]['items'][] = [
                    'client' => $bonCommande->client->raison_sociale,
                    'quantite' => $article->quantite,
                    'unite' => 'KG', // Default unit
                    'ville' => $bonCommande->ville_livraison ?? '',
                    'telephone' => $bonCommande->client->telephone ?? '',
                ];
            }
        }

        // Calculate totals per group
        foreach ($groupedItems as $key => $group) {
            $groupedItems[$key]['total'] = array_sum(array_column($group['items'], 'quantite'));
        }

        // Generate bon de charge number (format: BC2026/144)
        $year = date('Y');
        // Try to find existing bon de charge numbers or use a simple increment
        $lastBon = BonCommandeClient::where('numero_bon', 'like', "BC-{$year}%")
            ->orderBy('numero_bon', 'desc')
            ->first();
        
        if ($lastBon) {
            preg_match('/BC-\d{4}\/(\d+)/', $lastBon->numero_bon, $matches);
            $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 144;
        } else {
            $nextNumber = 144; // Default starting number
        }
        $numeroBonCharge = "BC{$year}/" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('ventes.bon-charge-print', [
            'bonCommandes' => $bonCommandes,
            'groupedItems' => $groupedItems,
            'numeroBonCharge' => $numeroBonCharge,
            'date' => now(),
        ]);
    }

    /**
     * Validate a Bon de commande
     */
    public function validateBon($id)
    {
        try {
            $bonCommande = BonCommandeClient::findOrFail($id);
            
            if ($bonCommande->statut === 'Validé') {
                return response()->json(['message' => 'Ce bon de commande est déjà validé'], 400);
            }
            
            if ($bonCommande->statut === 'Converti') {
                return response()->json(['message' => 'Ce bon de commande a déjà été converti en bon de livraison'], 400);
            }

            if ($bonCommande->statut === 'Annulé') {
                return response()->json(['message' => 'Ce bon de commande a été annulé'], 400);
            }
            
            $bonCommande->update(['statut' => 'Validé']);
            
            return response()->json([
                'message' => 'Bon de commande validé avec succès',
                'bonCommande' => $bonCommande->load('client', 'articles'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la validation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel a Bon de commande
     */
    public function cancel(Request $request, $id)
    {
        try {
            $bonCommande = BonCommandeClient::findOrFail($id);
            
            if ($bonCommande->statut === 'Converti') {
                return response()->json(['message' => 'Ce bon de commande a déjà été converti en bon de livraison'], 400);
            }
            
            $bonCommande->update([
                'statut' => 'Annulé',
                'motif_annulation' => $request->motif_annulation,
            ]);
            
            return response()->json([
                'message' => 'Bon de commande annulé avec succès',
                'bonCommande' => $bonCommande->load('client', 'articles'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'annulation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Convert a validated Bon de commande to Bon de livraison
     */
    public function convertToBonLivraison($id)
    {
        try {
            DB::beginTransaction();
            
            $bonCommande = BonCommandeClient::with(['client', 'articles'])->findOrFail($id);
            
            if ($bonCommande->statut !== 'Validé') {
                return response()->json(['error' => 'Seuls les bons de commande validés peuvent être convertis en bon de livraison'], 400);
            }
            
            // Generate next bon de livraison number
            $year = date('Y');
            $lastBon = BonLivraisonClient::where('numero_bon', 'like', "BL-{$year}%")
                ->orderBy('numero_bon', 'desc')
                ->first();
            
            if ($lastBon) {
                preg_match('/BL-\d{4}\/(\d+)/', $lastBon->numero_bon, $matches);
                $lastNumber = $matches[1] ?? 0;
                $nextNumber = str_pad((int)$lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '0001';
            }
            $numeroBonLivraison = "BL-{$year}/{$nextNumber}";
            
            // Create the Bon de livraison
            $bonLivraison = BonLivraisonClient::create([
                'numero_bon' => $numeroBonLivraison,
                'date' => now(),
                'client_id' => $bonCommande->client_id,
                'bon_commande_id' => $bonCommande->id,
                'mode_paiement' => $bonCommande->mode_paiement,
                'echeance' => $bonCommande->echeance,
                'date_echeance' => $bonCommande->echeance,
                'ville_livraison' => $bonCommande->ville_livraison ?? $bonCommande->client->ville,
                'total_quantites' => $bonCommande->total_quantites,
                'total_general' => $bonCommande->total_general,
                'statut' => 'En attente',
            ]);
            
            // Create articles for the Bon de livraison
            foreach ($bonCommande->articles as $article) {
                $bonLivraison->articles()->create([
                    'code_article' => $article->code_article,
                    'designation' => $article->designation,
                    'quantite' => $article->quantite,
                    'prix_unitaire' => $article->prix_unitaire,
                    'sous_total' => $article->sous_total,
                ]);
            }
            
            // Update bon de commande status to "Converti"
            $bonCommande->update(['statut' => 'Converti']);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Bon de commande converti en bon de livraison avec succès',
                'bonLivraison' => $bonLivraison->load('client', 'articles'),
                'bonCommande' => $bonCommande,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la conversion: ' . $e->getMessage()], 500);
        }
    }
}

