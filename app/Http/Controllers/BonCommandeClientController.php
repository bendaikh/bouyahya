<?php

namespace App\Http\Controllers;

use App\Models\BonCommandeClient;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonCommandeClientController extends Controller
{
    public function index()
    {
        $bonCommandes = BonCommandeClient::with('client')->orderBy('created_at', 'desc')->get();
        $clients = Client::orderBy('raison_sociale')->get();
        
        return view('ventes.bon-commande', [
            'page_title' => 'Bon de commande',
            'bonCommandes' => $bonCommandes,
            'clients' => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'numero_bon' => 'required|string|unique:bon_commande_clients,numero_bon',
            'client_id' => 'required|exists:clients,id',
            'mode_paiement' => 'required|string',
            'echeance' => 'required|string',
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
                'mode_paiement' => $request->mode_paiement,
                'echeance' => $request->echeance,
                'total_quantites' => $totalQuantites,
                'total_general' => $totalGeneral,
                'statut' => 'En attente',
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
            return response()->json(['numero' => 'BC-' . $year . '-0001']);
        }

        preg_match('/BC-\d{4}-(\d+)/', $lastBon->numero_bon, $matches);
        $lastNumber = $matches[1] ?? 0;
        $nextNumber = (int)$lastNumber + 1;

        return response()->json(['numero' => 'BC-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT)]);
    }

    public function show($id)
    {
        $bonCommande = BonCommandeClient::with('articles')->findOrFail($id);
        return response()->json($bonCommande);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'numero_bon' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'mode_paiement' => 'required|string',
            'echeance' => 'required|string',
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
                'mode_paiement' => $request->mode_paiement,
                'echeance' => $request->echeance,
                'total_quantites' => $totalQuantites,
                'total_general' => $totalGeneral,
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
}

