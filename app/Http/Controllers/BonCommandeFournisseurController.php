<?php

namespace App\Http\Controllers;

use App\Models\BonCommandeFournisseur;
use App\Models\BonAchatFournisseur;
use App\Models\BonAchatArticle;
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

    /**
     * Validate a Bon de commande
     */
    public function validate($id)
    {
        try {
            $bonCommande = BonCommandeFournisseur::findOrFail($id);
            
            if ($bonCommande->statut === 'Validé') {
                return response()->json(['message' => 'Ce bon de commande est déjà validé'], 400);
            }
            
            if ($bonCommande->statut === 'Converti') {
                return response()->json(['message' => 'Ce bon de commande a déjà été converti en bon d\'achat'], 400);
            }
            
            $bonCommande->update(['statut' => 'Validé']);
            
            return response()->json([
                'message' => 'Bon de commande validé avec succès',
                'bonCommande' => $bonCommande->load('fournisseur', 'articles'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la validation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Convert a validated Bon de commande to Bon d'achat Fournisseur
     */
    public function convertToBonAchat($id)
    {
        try {
            DB::beginTransaction();
            
            $bonCommande = BonCommandeFournisseur::with(['fournisseur', 'articles'])->findOrFail($id);
            
            if ($bonCommande->statut !== 'Validé') {
                return response()->json(['error' => 'Seuls les bons de commande validés peuvent être convertis en bon d\'achat'], 400);
            }
            
            // Generate next bon d'achat number
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
            $numeroBonAchat = "BF-{$year}{$nextNumber}";
            
            // Create the Bon d'achat
            $bonAchat = BonAchatFournisseur::create([
                'numero_bon' => $numeroBonAchat,
                'date' => now(),
                'fournisseur_id' => $bonCommande->fournisseur_id,
                'type_paiement' => $bonCommande->mode_paiement,
                'echeance' => $bonCommande->echeance,
                'client_livre' => null,
                'famille' => 'Général',
                'ville' => $bonCommande->fournisseur->ville ?? null,
                'chauffeur' => null,
                'matricule' => null,
                'sous_total_ttc' => $bonCommande->total_general,
                'total_qte' => $bonCommande->total_quantites,
                'total_ttc' => $bonCommande->total_general,
                'statut' => 'brouillon',
                'bon_commande_id' => $bonCommande->id,
            ]);
            
            // Create articles for the Bon d'achat
            foreach ($bonCommande->articles as $article) {
                BonAchatArticle::create([
                    'bon_achat_id' => $bonAchat->id,
                    'ref_article' => $article->code_article,
                    'designation_article' => $article->designation,
                    'qte' => $article->quantite,
                    'prix_unitaire_ttc' => $article->prix_unitaire,
                    'total' => $article->sous_total,
                ]);
            }
            
            // Update bon de commande status to "Converti"
            $bonCommande->update(['statut' => 'Converti']);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Bon de commande converti en bon d\'achat avec succès',
                'bonAchat' => $bonAchat->load('fournisseur', 'articles'),
                'bonCommande' => $bonCommande,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la conversion: ' . $e->getMessage()], 500);
        }
    }
}

