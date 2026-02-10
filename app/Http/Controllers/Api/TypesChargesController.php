<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeCharge;
use App\Models\ChargeEntry;
use App\Models\CompteTresorerie;
use App\Models\Setting;
use App\Traits\UsesSelectedYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TypesChargesController extends Controller
{
    use UsesSelectedYear;

    /**
     * Get all charge entries with related data
     */
    public function index()
    {
        $selectedYear = $this->getSelectedYear();
        
        // Get types from settings (types_reglement)
        $typesReglement = json_decode(Setting::getValue('types_reglement', '[]'), true);
        $typesMap = collect($typesReglement)->keyBy('id');

        $charges = ChargeEntry::with(['compteCaisse'])
            ->whereYear('date', $selectedYear)
            ->orderBy('date', 'desc')
            ->orderBy('heure', 'desc')
            ->get()
            ->map(function ($charge) use ($typesMap) {
                // Lookup type libelle from settings
                $typeLibelle = null;
                if ($charge->type_id && isset($typesMap[$charge->type_id])) {
                    $typeLibelle = $typesMap[$charge->type_id]['libelle'] ?? null;
                }

                return [
                    'id' => $charge->id,
                    'reference' => $charge->reference,
                    'date' => $charge->date,
                    'heure' => $charge->heure,
                    'operateur' => $charge->operateur,
                    'compte_caisse_id' => $charge->compte_caisse_id,
                    'compte_caisse_libelle' => $charge->compteCaisse?->libelle,
                    'type_id' => $charge->type_id,
                    'type_libelle' => $typeLibelle,
                    'numero' => $charge->numero,
                    'libelle' => $charge->libelle,
                    'beneficiaire' => $charge->beneficiaire,
                    'montant' => $charge->montant,
                    'observation' => $charge->observation,
                    'created_at' => $charge->created_at,
                ];
            });

        return response()->json(['charges' => $charges]);
    }

    /**
     * Get all charge types for dropdown
     */
    public function getTypes()
    {
        $types = TypeCharge::where('actif', true)
            ->orderBy('libelle')
            ->get(['id', 'code', 'libelle']);

        return response()->json(['types' => $types]);
    }

    /**
     * Store a new charge entry
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'operateur' => 'nullable|string|max:255',
            'compte_caisse_id' => 'nullable|exists:compte_tresoreries,id',
            'type_id' => 'nullable|string|max:255', // Type from settings (types_reglement)
            'numero' => 'nullable|string|max:255',
            'libelle' => 'required|string|max:255',
            'beneficiaire' => 'nullable|string|max:255',
            'montant' => 'required|numeric|min:0',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $charge = ChargeEntry::create([
            'reference' => ChargeEntry::generateReference(),
            'date' => now()->toDateString(),
            'heure' => now()->format('H:i:s'),
            'operateur' => $request->operateur,
            'compte_caisse_id' => $request->compte_caisse_id,
            'type_id' => $request->type_id,
            'numero' => $request->numero,
            'libelle' => $request->libelle,
            'beneficiaire' => $request->beneficiaire,
            'montant' => $request->montant,
            'observation' => $request->observation,
        ]);

        return response()->json([
            'message' => 'Charge créée avec succès',
            'charge' => $charge->load(['type', 'compteCaisse'])
        ], 201);
    }

    /**
     * Get a specific charge entry
     */
    public function show($id)
    {
        $charge = ChargeEntry::with(['type', 'compteCaisse'])->findOrFail($id);

        return response()->json(['charge' => $charge]);
    }

    /**
     * Update a charge entry
     */
    public function update(Request $request, $id)
    {
        $charge = ChargeEntry::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'operateur' => 'nullable|string|max:255',
            'compte_caisse_id' => 'nullable|exists:compte_tresoreries,id',
            'type_id' => 'nullable|string|max:255', // Type from settings (types_reglement)
            'numero' => 'nullable|string|max:255',
            'libelle' => 'required|string|max:255',
            'beneficiaire' => 'nullable|string|max:255',
            'montant' => 'required|numeric|min:0',
            'observation' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $charge->update([
            'operateur' => $request->operateur,
            'compte_caisse_id' => $request->compte_caisse_id,
            'type_id' => $request->type_id,
            'numero' => $request->numero,
            'libelle' => $request->libelle,
            'beneficiaire' => $request->beneficiaire,
            'montant' => $request->montant,
            'observation' => $request->observation,
        ]);

        return response()->json([
            'message' => 'Charge mise à jour avec succès',
            'charge' => $charge->load(['type', 'compteCaisse'])
        ]);
    }

    /**
     * Delete a charge entry
     */
    public function destroy($id)
    {
        $charge = ChargeEntry::findOrFail($id);
        $charge->delete();

        return response()->json(['message' => 'Charge supprimée avec succès']);
    }

    /**
     * Print a charge entry (generate PDF or print view)
     */
    public function print($id)
    {
        $charge = ChargeEntry::with(['type', 'compteCaisse'])->findOrFail($id);

        // Return a print-friendly HTML view
        return view('tresorerie.print-charge', compact('charge'));
    }

    // =====================================================
    // TYPE CHARGE MANAGEMENT (for settings)
    // =====================================================

    /**
     * Get all type charges (for management)
     */
    public function indexTypes()
    {
        $types = TypeCharge::orderBy('libelle')->get();
        return response()->json(['types' => $types]);
    }

    /**
     * Store a new type charge
     */
    public function storeType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:types_charges,code',
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'message' => 'Ce code existe déjà'], 422);
        }

        $type = TypeCharge::create([
            'code' => $request->code,
            'libelle' => $request->libelle,
            'description' => $request->description,
            'actif' => true,
        ]);

        return response()->json([
            'message' => 'Type de charge créé avec succès',
            'type' => $type
        ], 201);
    }

    /**
     * Update a type charge
     */
    public function updateType(Request $request, $id)
    {
        $type = TypeCharge::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:types_charges,code,' . $id,
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'actif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'message' => 'Ce code existe déjà'], 422);
        }

        $type->update([
            'code' => $request->code,
            'libelle' => $request->libelle,
            'description' => $request->description,
            'actif' => $request->actif ?? $type->actif,
        ]);

        return response()->json([
            'message' => 'Type de charge mis à jour avec succès',
            'type' => $type
        ]);
    }

    /**
     * Delete a type charge
     */
    public function destroyType($id)
    {
        $type = TypeCharge::findOrFail($id);

        // Check if type has associated charges
        if ($type->chargeEntries()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer ce type car il est utilisé par des charges'
            ], 422);
        }

        $type->delete();

        return response()->json(['message' => 'Type de charge supprimé avec succès']);
    }
}




