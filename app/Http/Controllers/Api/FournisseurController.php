<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fournisseurs = Fournisseur::orderBy('created_at', 'desc')->get();
        return response()->json($fournisseurs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom_fournisseur' => 'required|string|max:255',
            'nom_gerant' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate code if not provided
        $codeFournisseur = $request->code_fournisseur ?? $this->generateFournisseurCode();

        $fournisseur = Fournisseur::create([
            'code_fournisseur' => $codeFournisseur,
            'nom_fournisseur' => $request->nom_fournisseur,
            'nom_gerant' => $request->nom_gerant,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'activite' => $request->activite,
            'ville' => $request->ville,
            'ice' => $request->ice,
            'mode_paiement' => $request->mode_paiement,
        ]);

        return response()->json($fournisseur, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        return response()->json($fournisseur);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nom_fournisseur' => 'required|string|max:255',
            'nom_gerant' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fournisseur->update([
            'nom_fournisseur' => $request->nom_fournisseur,
            'nom_gerant' => $request->nom_gerant,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'activite' => $request->activite,
            'ville' => $request->ville,
            'ice' => $request->ice,
            'mode_paiement' => $request->mode_paiement,
        ]);

        return response()->json($fournisseur);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->delete();
        return response()->json(['message' => 'Fournisseur deleted successfully']);
    }

    /**
     * Generate next fournisseur code
     */
    private function generateFournisseurCode(): string
    {
        $lastFournisseur = Fournisseur::orderBy('id', 'desc')->first();
        
        if (!$lastFournisseur) {
            return 'F-00001';
        }

        // Extract number from code (e.g., 'F-00001' -> 1)
        preg_match('/F-(\d+)/', $lastFournisseur->code_fournisseur, $matches);
        $lastNumber = $matches[1] ?? 0;
        $nextNumber = (int)$lastNumber + 1;

        return 'F-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get next fournisseur code (for frontend)
     */
    public function nextCode()
    {
        return response()->json(['code' => $this->generateFournisseurCode()]);
    }
}
