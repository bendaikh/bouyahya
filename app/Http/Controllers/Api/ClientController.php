<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::orderBy('created_at', 'desc')->get();
        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'raison_sociale' => 'required|string|max:255',
            'nom_gerant' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'type_client' => 'required|string|in:REV,PROMO,ENTR,CON.FI',
            'mode_paiement' => 'required|string',
            'echeance' => 'required|string',
            'plafond' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate code if not provided
        $codeClient = $request->code_client ?? $this->generateClientCode();

        $client = Client::create([
            'code_client' => $codeClient,
            'raison_sociale' => $request->raison_sociale,
            'nom_gerant' => $request->nom_gerant,
            'ville' => $request->ville,
            'telephone' => $request->telephone,
            'type_client' => $request->type_client,
            'mode_paiement' => $request->mode_paiement,
            'echeance' => $request->echeance,
            'cin' => $request->cin,
            'if_fiscal' => $request->if_fiscal,
            'patente' => $request->patente,
            'cnss' => $request->cnss,
            'ice' => $request->ice,
            'banque' => $request->banque,
            'rib' => $request->rib,
            'plafond' => $request->plafond,
            'bloquer' => $request->boolean('bloquer', false),
        ]);

        return response()->json($client, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $client = Client::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'raison_sociale' => 'required|string|max:255',
            'nom_gerant' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'type_client' => 'required|string|in:REV,PROMO,ENTR,CON.FI',
            'mode_paiement' => 'required|string',
            'echeance' => 'required|string',
            'plafond' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $client->update([
            'raison_sociale' => $request->raison_sociale,
            'nom_gerant' => $request->nom_gerant,
            'ville' => $request->ville,
            'telephone' => $request->telephone,
            'type_client' => $request->type_client,
            'mode_paiement' => $request->mode_paiement,
            'echeance' => $request->echeance,
            'cin' => $request->cin,
            'if_fiscal' => $request->if_fiscal,
            'patente' => $request->patente,
            'cnss' => $request->cnss,
            'ice' => $request->ice,
            'banque' => $request->banque,
            'rib' => $request->rib,
            'plafond' => $request->plafond,
            'bloquer' => $request->boolean('bloquer', $client->bloquer),
        ]);

        return response()->json($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return response()->json(['message' => 'Client deleted successfully']);
    }

    /**
     * Generate next client code
     */
    private function generateClientCode(): string
    {
        $lastClient = Client::orderBy('id', 'desc')->first();
        
        if (!$lastClient) {
            return 'C-0001';
        }

        // Extract number from code (e.g., 'C-0001' -> 1)
        preg_match('/C-(\d+)/', $lastClient->code_client, $matches);
        $lastNumber = $matches[1] ?? 0;
        $nextNumber = (int)$lastNumber + 1;

        return 'C-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get next client code (for frontend)
     */
    public function nextCode()
    {
        return response()->json(['code' => $this->generateClientCode()]);
    }
}
