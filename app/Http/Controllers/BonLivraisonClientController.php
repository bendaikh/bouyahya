<?php

namespace App\Http\Controllers;

use App\Models\BonLivraisonClient;
use App\Models\Client;
use App\Models\Article;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonLivraisonClientController extends Controller
{
    public function index()
    {
        $bonLivraisons = BonLivraisonClient::with(['client', 'bonCommande'])->orderBy('created_at', 'desc')->get();
        $clients = Client::orderBy('raison_sociale')->get();
        $articles = Article::where('actif', true)->orderBy('designation')->get();
        
        // Get cities from settings
        $cities = json_decode(Setting::getValue('cities', '[]'), true);
        
        return view('ventes.bon-livraison', [
            'page_title' => 'Bon de livraison',
            'bonLivraisons' => $bonLivraisons,
            'clients' => $clients,
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
            'mode_paiement' => 'required|string',
            'echeance' => 'nullable|string',
            'date_echeance' => 'nullable|date',
            'ville_livraison' => 'nullable|string',
            'chauffeur' => 'nullable|string',
            'matricule_vehicule' => 'nullable|string',
            'telephone_chauffeur' => 'nullable|string',
            'adresse_livraison' => 'nullable|string',
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
                'mode_paiement' => $request->mode_paiement,
                'echeance' => $request->echeance,
                'date_echeance' => $request->date_echeance,
                'ville_livraison' => $request->ville_livraison,
                'chauffeur' => $request->chauffeur,
                'matricule_vehicule' => $request->matricule_vehicule,
                'telephone_chauffeur' => $request->telephone_chauffeur,
                'adresse_livraison' => $request->adresse_livraison,
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
        $bonLivraison = BonLivraisonClient::with(['articles', 'client', 'bonCommande'])->findOrFail($id);
        return response()->json($bonLivraison);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'numero_bon' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'mode_paiement' => 'required|string',
            'echeance' => 'nullable|string',
            'date_echeance' => 'nullable|date',
            'ville_livraison' => 'nullable|string',
            'chauffeur' => 'nullable|string',
            'matricule_vehicule' => 'nullable|string',
            'telephone_chauffeur' => 'nullable|string',
            'adresse_livraison' => 'nullable|string',
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
                'mode_paiement' => $request->mode_paiement,
                'echeance' => $request->echeance,
                'date_echeance' => $request->date_echeance,
                'ville_livraison' => $request->ville_livraison,
                'chauffeur' => $request->chauffeur,
                'matricule_vehicule' => $request->matricule_vehicule,
                'telephone_chauffeur' => $request->telephone_chauffeur,
                'adresse_livraison' => $request->adresse_livraison,
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
        
        return view('ventes.bon-livraison-print', [
            'bonLivraison' => $bonLivraison,
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
}

