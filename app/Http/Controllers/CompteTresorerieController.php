<?php

namespace App\Http\Controllers;

use App\Models\CompteTresorerie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompteTresorerieController extends Controller
{
    public function index()
    {
        $comptes = CompteTresorerie::orderBy('created_at', 'desc')->get();

        return view('tresorerie.compte-bancaire', [
            'page_title' => 'Trésorerie',
            'comptes' => $comptes,
        ]);
    }

    /**
     * Ventes: Trésorerie (renamed from "Règlements clients")
     */
    public function indexVentes()
    {
        $comptes = CompteTresorerie::orderBy('created_at', 'desc')->get();

        return view('ventes.reglements-clients', [
            'page_title' => 'Trésorerie',
            'comptes' => $comptes,
        ]);
    }

    public function nextCode()
    {
        $year = date('Y');
        $last = CompteTresorerie::where('code', 'like', "CT-{$year}/%")
            ->orderBy('id', 'desc')
            ->first();

        if (!$last) {
            return response()->json(['code' => "CT-{$year}/0001"]);
        }

        preg_match('/CT-\d{4}\/(\d+)/', $last->code, $matches);
        $lastNumber = (int)($matches[1] ?? 0);
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return response()->json(['code' => "CT-{$year}/{$nextNumber}"]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:compte_tresoreries,code',
            'date_creation' => 'required|date',
            'libelle' => 'required|string',
            'type_compte' => 'required|in:banque,caisse',
            'agence' => 'nullable|string',
            'ville' => 'nullable|string',
            'adresse' => 'nullable|string',
            'solde_initial' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $soldeInitial = (float)($request->solde_initial ?? 0);

        $compte = CompteTresorerie::create([
            'code' => $request->code,
            'date_creation' => $request->date_creation,
            'libelle' => $request->libelle,
            'type_compte' => $request->type_compte,
            'agence' => $request->agence,
            'ville' => $request->ville,
            'adresse' => $request->adresse,
            'solde_initial' => $soldeInitial,
            'solde_actuel' => $soldeInitial,
        ]);

        return response()->json([
            'message' => 'Compte de trésorerie créé avec succès',
            'compte' => $compte,
        ], 201);
    }

    public function destroy($id)
    {
        $compte = CompteTresorerie::findOrFail($id);
        $compte->delete();

        return response()->json(['message' => 'Compte supprimé avec succès']);
    }
}


