<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    /**
     * Get all articles
     */
    public function index(Request $request)
    {
        $query = Article::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        // Filter by famille
        if ($request->has('famille_id') && $request->famille_id) {
            $query->where('famille_id', $request->famille_id);
        }

        // Filter by actif status
        if ($request->has('actif')) {
            $query->where('actif', $request->actif === 'true' || $request->actif === '1');
        }

        $articles = $query->orderBy('created_at', 'desc')->get();

        // Add famille and sous-famille names
        $familles = json_decode(Setting::getValue('familles_article', '[]'), true);
        $sousFamilles = json_decode(Setting::getValue('sous_familles_article', '[]'), true);
        $unites = json_decode(Setting::getValue('unites_mesure', '[]'), true);

        $articles = $articles->map(function ($article) use ($familles, $sousFamilles, $unites) {
            $articleData = $article->toArray();
            
            // Find famille name
            $articleData['famille_nom'] = null;
            foreach ($familles as $famille) {
                if ($famille['id'] === $article->famille_id) {
                    $articleData['famille_nom'] = $famille['nom'];
                    break;
                }
            }

            // Find sous-famille name
            $articleData['sous_famille_nom'] = null;
            foreach ($sousFamilles as $sousFamille) {
                if ($sousFamille['id'] === $article->sous_famille_id) {
                    $articleData['sous_famille_nom'] = $sousFamille['nom'];
                    break;
                }
            }

            // Find unite mesure
            $articleData['unite_mesure'] = null;
            foreach ($unites as $unite) {
                if ($unite['id'] === $article->unite_mesure_id) {
                    $articleData['unite_mesure'] = $unite;
                    break;
                }
            }

            return $articleData;
        });

        return response()->json([
            'articles' => $articles,
            'total' => $articles->count()
        ]);
    }

    /**
     * Get next reference number
     */
    public function nextReference()
    {
        return response()->json([
            'reference' => Article::generateNextReference()
        ]);
    }

    /**
     * Store a new article
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string|max:50|unique:articles,reference',
            'designation' => 'required|string|max:255',
            'famille_id' => 'nullable|string',
            'sous_famille_id' => 'nullable|string',
            'unite_mesure_id' => 'nullable|string',
            'tva' => 'nullable|numeric|min:0|max:100',
            'autoriser_stock_negatif' => 'boolean',
            'prix_achat' => 'nullable|numeric|min:0',
            'prix_vente' => 'nullable|numeric|min:0',
            'stock_minimum' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $article = Article::create([
            'reference' => $request->reference,
            'designation' => $request->designation,
            'famille_id' => $request->famille_id,
            'sous_famille_id' => $request->sous_famille_id,
            'unite_mesure_id' => $request->unite_mesure_id,
            'tva' => $request->tva ?? 20,
            'autoriser_stock_negatif' => $request->autoriser_stock_negatif ?? false,
            'prix_achat' => $request->prix_achat ?? 0,
            'prix_vente' => $request->prix_vente ?? 0,
            'stock_minimum' => $request->stock_minimum ?? 0,
            'actif' => true,
        ]);

        return response()->json([
            'message' => 'Article créé avec succès',
            'article' => $article
        ], 201);
    }

    /**
     * Get a specific article
     */
    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article non trouvé'], 404);
        }

        // Get reference data
        $familles = json_decode(Setting::getValue('familles_article', '[]'), true);
        $sousFamilles = json_decode(Setting::getValue('sous_familles_article', '[]'), true);
        $unites = json_decode(Setting::getValue('unites_mesure', '[]'), true);

        $articleData = $article->toArray();

        // Find famille name
        foreach ($familles as $famille) {
            if ($famille['id'] === $article->famille_id) {
                $articleData['famille_nom'] = $famille['nom'];
                break;
            }
        }

        // Find sous-famille name
        foreach ($sousFamilles as $sousFamille) {
            if ($sousFamille['id'] === $article->sous_famille_id) {
                $articleData['sous_famille_nom'] = $sousFamille['nom'];
                break;
            }
        }

        // Find unite mesure
        foreach ($unites as $unite) {
            if ($unite['id'] === $article->unite_mesure_id) {
                $articleData['unite_mesure'] = $unite;
                break;
            }
        }

        return response()->json($articleData);
    }

    /**
     * Update an article
     */
    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'reference' => 'required|string|max:50|unique:articles,reference,' . $id,
            'designation' => 'required|string|max:255',
            'famille_id' => 'nullable|string',
            'sous_famille_id' => 'nullable|string',
            'unite_mesure_id' => 'nullable|string',
            'tva' => 'nullable|numeric|min:0|max:100',
            'autoriser_stock_negatif' => 'boolean',
            'prix_achat' => 'nullable|numeric|min:0',
            'prix_vente' => 'nullable|numeric|min:0',
            'stock_minimum' => 'nullable|integer|min:0',
            'actif' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $article->update([
            'reference' => $request->reference,
            'designation' => $request->designation,
            'famille_id' => $request->famille_id,
            'sous_famille_id' => $request->sous_famille_id,
            'unite_mesure_id' => $request->unite_mesure_id,
            'tva' => $request->tva ?? $article->tva,
            'autoriser_stock_negatif' => $request->autoriser_stock_negatif ?? $article->autoriser_stock_negatif,
            'prix_achat' => $request->prix_achat ?? $article->prix_achat,
            'prix_vente' => $request->prix_vente ?? $article->prix_vente,
            'stock_minimum' => $request->stock_minimum ?? $article->stock_minimum,
            'actif' => $request->actif ?? $article->actif,
        ]);

        return response()->json([
            'message' => 'Article mis à jour avec succès',
            'article' => $article
        ]);
    }

    /**
     * Delete an article
     */
    public function destroy($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article non trouvé'], 404);
        }

        // Check if article has stock
        if ($article->stock_actuel > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un article avec du stock'
            ], 422);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article supprimé avec succès'
        ]);
    }

    /**
     * Get reference data (familles, sous-familles, unites)
     */
    public function getReferenceData()
    {
        return response()->json([
            'familles' => json_decode(Setting::getValue('familles_article', '[]'), true),
            'sous_familles' => json_decode(Setting::getValue('sous_familles_article', '[]'), true),
            'unites_mesure' => json_decode(Setting::getValue('unites_mesure', '[]'), true),
        ]);
    }
}

