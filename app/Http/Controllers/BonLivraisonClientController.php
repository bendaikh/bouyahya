<?php

namespace App\Http\Controllers;

use App\Models\BonLivraisonClient;
use App\Models\BonCommandeClient;
use App\Models\BonAchatFournisseur;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Article;
use App\Models\BonAchatArticle;
use App\Models\BonLivraisonClientArticle;
use App\Models\Setting;
use App\Models\ReglementClient;
use App\Models\ReglementClientLigne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonLivraisonClientController extends Controller
{
    public function index()
    {
        $bonLivraisons = BonLivraisonClient::with(['client', 'bonCommande', 'bonAchatFournisseur'])
            ->withSum('reglementLignes as montant_paye', 'montant_regle')
            ->orderBy('created_at', 'desc')
            ->get();
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
        
        return view('ventes.bon-livraison', [
            'page_title' => 'Bon de livraison',
            'bonLivraisons' => $bonLivraisons,
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
            'numero_bon' => 'required|string|unique:bon_livraison_clients,numero_bon',
            'client_id' => 'required|exists:clients,id',
            'bon_commande_id' => 'nullable|exists:bon_commande_clients,id',
            'bon_achat_fournisseur_id' => 'nullable|exists:bon_achat_fournisseur,id',
            'mode_paiement' => 'required|string',
            'mode_reglement' => 'nullable|string',
            'delai_reglement' => 'nullable|string',
            'transporteur' => 'nullable|string',
            'commercial' => 'nullable|string',
            'situation' => 'nullable|string',
            'vehicule' => 'nullable|string',
            'echeance' => 'nullable|string',
            'date_echeance' => 'nullable|date',
            'ville_livraison' => 'nullable|string',
            'chauffeur' => 'nullable|string',
            'matricule_vehicule' => 'nullable|string',
            'observations' => 'nullable|string',
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

            $bonLivraison = BonLivraisonClient::create([
                'numero_bon' => $request->numero_bon,
                'date' => $request->date,
                'client_id' => $request->client_id,
                'bon_commande_id' => $request->bon_commande_id,
                'bon_achat_fournisseur_id' => $request->bon_achat_fournisseur_id,
                'mode_paiement' => $request->mode_paiement,
                'mode_reglement' => $request->mode_reglement,
                'delai_reglement' => $request->delai_reglement,
                'transporteur' => $request->transporteur,
                'commercial' => $request->commercial,
                'situation' => $request->situation,
                'vehicule' => $request->vehicule,
                'echeance' => $request->echeance,
                'date_echeance' => $request->date_echeance,
                'ville_livraison' => $request->ville_livraison,
                'chauffeur' => $request->chauffeur,
                'matricule_vehicule' => $request->matricule_vehicule,
                'observations' => $request->observations,
                'total_quantites' => $totalQuantites,
                'total_general' => $totalGeneral,
                'statut' => 'En attente',
            ]);

            foreach ($request->items as $item) {
                $bonLivraison->articles()->create([
                    'code_article' => $item['code_article'],
                    'designation' => $item['designation'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'sous_total' => $item['sous_total'],
                ]);
            }

            // Handle payment if provided
            if ($request->has('payment') && !empty($request->payment)) {
                $paymentData = $request->payment;
                
                // Generate the next code_reglement
                $lastReglement = ReglementClient::orderBy('id', 'desc')->first();
                $nextNumber = $lastReglement ? intval(substr($lastReglement->code_reglement, 3)) + 1 : 1;
                $codeReglement = 'RC-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                $reglement = ReglementClient::create([
                    'code_reglement' => $codeReglement,
                    'date_reglement' => $request->date,
                    'client_id' => $request->client_id,
                    'type_reglement' => $paymentData['modeReglement'],
                    'numero_piece' => $paymentData['reference'] ?? null,
                    'banque' => $paymentData['banque'] ?? null,
                    'nom_tire' => $paymentData['nomTire'] ?? null,
                    'tresorerie_id' => $paymentData['tresorerieId'] ?? null,
                    'montant' => $paymentData['montant'],
                    'date_encaissement' => $paymentData['echeance'] ?? null,
                    'statut' => 'paye', // Direct payment is considered paid
                    'observation' => 'Paiement direct lors de la création du BL',
                ]);

                ReglementClientLigne::create([
                    'reglement_id' => $reglement->id,
                    'bon_livraison_id' => $bonLivraison->id,
                    'montant_regle' => $paymentData['montant'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Bon de livraison créé avec succès',
                'bonLivraison' => $bonLivraison->load('client', 'articles'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la création du bon de livraison: ' . $e->getMessage()], 500);
        }
    }

    public function nextNumero()
    {
        $year = date('Y');
        $lastBon = BonLivraisonClient::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastBon) {
            return response()->json(['numero' => 'BL-' . $year . '/0001']);
        }

        preg_match('/BL-\d{4}\/(\d+)/', $lastBon->numero_bon, $matches);
        $lastNumber = $matches[1] ?? 0;
        $nextNumber = (int)$lastNumber + 1;

        return response()->json(['numero' => 'BL-' . $year . '/' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT)]);
    }

    public function show($id)
    {
        $bonLivraison = BonLivraisonClient::with(['articles', 'client', 'bonCommande', 'bonAchatFournisseur'])->findOrFail($id);
        return response()->json($bonLivraison);
    }

    /**
     * Get bon de commande clients for import
     */
    public function getBonCommandes()
    {
        // Get IDs of bon_commande_clients that are already imported
        $importedIds = BonLivraisonClient::whereNotNull('bon_commande_id')
            ->pluck('bon_commande_id')
            ->toArray();
        
        $bonCommandes = BonCommandeClient::with(['client', 'fournisseur'])
            // Accept common variants: Validé / Valide / valide / validé ...
            ->whereRaw("LOWER(statut) LIKE 'valid%'")
            // Exclude already imported bons
            ->whereNotIn('id', $importedIds)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($bonCommandes);
    }

    /**
     * Get bon d'achat fournisseurs for import
     */
    public function getBonAchatFournisseurs()
    {
        // Get IDs of bon_achat_fournisseur that are already imported
        $importedIds = BonLivraisonClient::whereNotNull('bon_achat_fournisseur_id')
            ->pluck('bon_achat_fournisseur_id')
            ->toArray();
        
        $bonAchats = BonAchatFournisseur::with(['fournisseur'])
            // Accept common variants: valide / validé / Validé / Valide ...
            ->whereRaw("LOWER(statut) LIKE 'valid%'")
            // Exclude already imported bons
            ->whereNotIn('id', $importedIds)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($bonAchats);
    }

    /**
     * Import from bon de commande client
     */
    public function importFromBonCommande($id)
    {
        $bonCommande = BonCommandeClient::with(['client', 'articles'])->findOrFail($id);
        
        return response()->json([
            'bon_commande_id' => $bonCommande->id,
            'numero_bon_commande' => $bonCommande->numero_bon,
            'client_id' => $bonCommande->client_id,
            'mode_paiement' => $bonCommande->mode_paiement,
            'echeance' => $bonCommande->echeance,
            'ville_livraison' => $bonCommande->ville_livraison,
            'items' => $bonCommande->articles->map(function($article) {
                return [
                    'code_article' => $article->code_article,
                    'designation' => $article->designation,
                    'quantite' => $article->quantite,
                    'prix_unitaire' => $article->prix_unitaire,
                    'sous_total' => $article->sous_total,
                ];
            })
        ]);
    }

    /**
     * Import from bon d'achat fournisseur
     */
    public function importFromBonAchatFournisseur($id)
    {
        $bonAchat = BonAchatFournisseur::with(['fournisseur', 'articles', 'bonCommande'])->findOrFail($id);
        
        return response()->json([
            'bon_achat_fournisseur_id' => $bonAchat->id,
            'numero_bon_achat' => $bonAchat->numero_bon,
            'numero_bon_commande' => $bonAchat->bonCommande ? $bonAchat->bonCommande->numero_bon : null,
            'client_id' => null, // Bon d'achat fournisseur doesn't have client_id
            'mode_paiement' => $bonAchat->type_paiement,
            'echeance' => $bonAchat->echeance,
            'ville_livraison' => $bonAchat->ville,
            'items' => $bonAchat->articles->map(function($article) {
                return [
                    'code_article' => $article->ref_article,
                    'designation' => $article->designation_article,
                    'quantite' => $article->qte,
                    'prix_unitaire' => $article->prix_unitaire_ttc,
                    'sous_total' => $article->total,
                ];
            })
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'numero_bon' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'bon_commande_id' => 'nullable|exists:bon_commande_clients,id',
            'bon_achat_fournisseur_id' => 'nullable|exists:bon_achat_fournisseur,id',
            'mode_paiement' => 'required|string',
            'mode_reglement' => 'nullable|string',
            'delai_reglement' => 'nullable|string',
            'transporteur' => 'nullable|string',
            'commercial' => 'nullable|string',
            'situation' => 'nullable|string',
            'vehicule' => 'nullable|string',
            'echeance' => 'nullable|string',
            'date_echeance' => 'nullable|date',
            'ville_livraison' => 'nullable|string',
            'chauffeur' => 'nullable|string',
            'matricule_vehicule' => 'nullable|string',
            'observations' => 'nullable|string',
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

            $bonLivraison = BonLivraisonClient::findOrFail($id);

            $totalQuantites = array_sum(array_column($request->items, 'quantite'));
            $totalGeneral = array_sum(array_column($request->items, 'sous_total'));

            $bonLivraison->update([
                'date' => $request->date,
                'numero_bon' => $request->numero_bon,
                'client_id' => $request->client_id,
                'bon_commande_id' => $request->bon_commande_id,
                'bon_achat_fournisseur_id' => $request->bon_achat_fournisseur_id,
                'mode_paiement' => $request->mode_paiement,
                'mode_reglement' => $request->mode_reglement,
                'delai_reglement' => $request->delai_reglement,
                'transporteur' => $request->transporteur,
                'commercial' => $request->commercial,
                'situation' => $request->situation,
                'vehicule' => $request->vehicule,
                'echeance' => $request->echeance,
                'date_echeance' => $request->date_echeance,
                'ville_livraison' => $request->ville_livraison,
                'chauffeur' => $request->chauffeur,
                'matricule_vehicule' => $request->matricule_vehicule,
                'observations' => $request->observations,
                'total_quantites' => $totalQuantites,
                'total_general' => $totalGeneral,
            ]);

            // Delete old articles and create new ones
            $bonLivraison->articles()->delete();
            
            foreach ($request->items as $item) {
                $bonLivraison->articles()->create([
                    'code_article' => $item['code_article'],
                    'designation' => $item['designation'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'sous_total' => $item['sous_total'],
                ]);
            }

            // Handle payment if provided during update (only if not already paid or to add new payment)
            if ($request->has('payment') && !empty($request->payment)) {
                $paymentData = $request->payment;
                
                // Generate the next code_reglement
                $lastReglement = ReglementClient::orderBy('id', 'desc')->first();
                $nextNumber = $lastReglement ? intval(substr($lastReglement->code_reglement, 3)) + 1 : 1;
                $codeReglement = 'RC-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                $reglement = ReglementClient::create([
                    'code_reglement' => $codeReglement,
                    'date_reglement' => $request->date,
                    'client_id' => $request->client_id,
                    'type_reglement' => $paymentData['modeReglement'],
                    'numero_piece' => $paymentData['reference'] ?? null,
                    'banque' => $paymentData['banque'] ?? null,
                    'nom_tire' => $paymentData['nomTire'] ?? null,
                    'tresorerie_id' => $paymentData['tresorerieId'] ?? null,
                    'montant' => $paymentData['montant'],
                    'date_encaissement' => $paymentData['echeance'] ?? null,
                    'statut' => 'paye',
                    'observation' => 'Paiement direct lors de la modification du BL',
                ]);

                ReglementClientLigne::create([
                    'reglement_id' => $reglement->id,
                    'bon_livraison_id' => $bonLivraison->id,
                    'montant_regle' => $paymentData['montant'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Bon de livraison modifié avec succès',
                'bonLivraison' => $bonLivraison->load('client', 'articles'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la modification du bon de livraison: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $bonLivraison = BonLivraisonClient::findOrFail($id);
            $bonLivraison->delete();
            
            return response()->json(['message' => 'Bon de livraison supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
        }
    }

    public function print($id)
    {
        $bonLivraison = BonLivraisonClient::with(['client', 'articles', 'bonCommande'])->findOrFail($id);
        $appLogo = Setting::getValue('app_logo', null);
        $appName = Setting::getValue('app_name', 'STE LES ATELIERS BOUYAHYA');
        
        return view('ventes.bon-livraison-print', [
            'bonLivraison' => $bonLivraison,
            'appLogo' => $appLogo,
            'appName' => $appName,
        ]);
    }

    /**
     * Mark as delivered
     */
    public function markDelivered($id)
    {
        try {
            $bonLivraison = BonLivraisonClient::findOrFail($id);
            
            if ($bonLivraison->statut === 'Livré') {
                return response()->json(['message' => 'Ce bon de livraison est déjà marqué comme livré'], 400);
            }
            
            $bonLivraison->update(['statut' => 'Livré']);
            
            return response()->json([
                'message' => 'Bon de livraison marqué comme livré',
                'bonLivraison' => $bonLivraison->load('client', 'articles'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel bon de livraison
     */
    public function cancel($id)
    {
        try {
            $bonLivraison = BonLivraisonClient::findOrFail($id);
            
            if ($bonLivraison->statut === 'Livré') {
                return response()->json(['message' => 'Impossible d\'annuler un bon de livraison déjà livré'], 400);
            }
            
            $bonLivraison->update(['statut' => 'Annulé']);
            
            return response()->json([
                'message' => 'Bon de livraison annulé',
                'bonLivraison' => $bonLivraison->load('client', 'articles'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update status of bon de livraison
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'statut' => 'required|string|in:En attente,Livré,Annulé',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $bonLivraison = BonLivraisonClient::findOrFail($id);
            $bonLivraison->update(['statut' => $request->statut]);
            
            return response()->json([
                'message' => 'Statut mis à jour avec succès',
                'bonLivraison' => $bonLivraison->load('client', 'articles'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
}

