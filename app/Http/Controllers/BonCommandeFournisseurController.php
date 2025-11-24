<?php

namespace App\Http\Controllers;

use App\Models\BonCommandeFournisseur;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonCommandeFournisseurController extends Controller
{
    public function index()
    {
        $bonCommandes = BonCommandeFournisseur::with('fournisseur')->orderBy('created_at', 'desc')->get();
        $fournisseurs = Fournisseur::orderBy('nom_fournisseur')->get();
        
        return view('achats.bon-commande', [
            'page_title' => 'Bon de commande',
            'bonCommandes' => $bonCommandes,
            'fournisseurs' => $fournisseurs,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'numero_bon' => 'required|string|unique:bon_commande_fournisseurs,numero_bon',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
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

            $bonCommande = BonCommandeFournisseur::create([
                'numero_bon' => $request->numero_bon,
                'date' => $request->date,
                'fournisseur_id' => $request->fournisseur_id,
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
                'bonCommande' => $bonCommande->load('fournisseur', 'articles'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la création du bon de commande: ' . $e->getMessage()], 500);
        }
    }

    public function nextNumero()
    {
        $year = date('Y');
        $lastBon = BonCommandeFournisseur::whereYear('created_at', $year)
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
        $bonCommande = BonCommandeFournisseur::with('articles')->findOrFail($id);
        return response()->json($bonCommande);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'numero_bon' => 'required|string',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
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

            $bonCommande = BonCommandeFournisseur::findOrFail($id);

            $totalQuantites = array_sum(array_column($request->items, 'quantite'));
            $totalGeneral = array_sum(array_column($request->items, 'sous_total'));

            $bonCommande->update([
                'date' => $request->date,
                'numero_bon' => $request->numero_bon,
                'fournisseur_id' => $request->fournisseur_id,
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
                'bonCommande' => $bonCommande->load('fournisseur', 'articles'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la modification du bon de commande: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $bonCommande = BonCommandeFournisseur::findOrFail($id);
            $bonCommande->delete();
            
            return response()->json(['message' => 'Bon de commande supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], 500);
        }
    }

    public function print($id)
    {
        $bonCommande = BonCommandeFournisseur::with(['fournisseur', 'articles'])->findOrFail($id);
        
        return view('achats.bon-commande-print', [
            'bonCommande' => $bonCommande,
        ]);
    }
}

