<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Get all settings
     */
    public function index()
    {
        $settings = [
            'app_name' => Setting::getValue('app_name', 'Bouyahya'),
            'app_logo' => Setting::getValue('app_logo', null),
            'cities' => json_decode(Setting::getValue('cities', '[]'), true),
            'familles_article' => json_decode(Setting::getValue('familles_article', '[]'), true),
            'sous_familles_article' => json_decode(Setting::getValue('sous_familles_article', '[]'), true),
            'unites_mesure' => json_decode(Setting::getValue('unites_mesure', '[]'), true),
            'commerciales' => json_decode(Setting::getValue('commerciales', '[]'), true),
            'banques' => \App\Models\Banque::orderBy('nom')->get()
        ];

        return response()->json($settings);
    }

    /**
     * Update app name
     */
    public function updateAppName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Setting::setValue('app_name', $request->app_name);

        return response()->json([
            'message' => 'Nom de l\'application mis à jour avec succès',
            'app_name' => $request->app_name
        ]);
    }

    /**
     * Upload app logo
     */
    public function uploadLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Delete old logo if exists
        $oldLogo = Setting::getValue('app_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }

        // Store new logo
        $logoPath = $request->file('logo')->store('logos', 'public');
        Setting::setValue('app_logo', $logoPath);

        return response()->json([
            'message' => 'Logo téléchargé avec succès',
            'logo_url' => Storage::url($logoPath),
            'logo_path' => $logoPath
        ]);
    }

    /**
     * Delete app logo
     */
    public function deleteLogo()
    {
        $logo = Setting::getValue('app_logo');
        
        if ($logo && Storage::disk('public')->exists($logo)) {
            Storage::disk('public')->delete($logo);
        }

        Setting::setValue('app_logo', null);

        return response()->json([
            'message' => 'Logo supprimé avec succès'
        ]);
    }

    /**
     * Update cities list
     */
    public function updateCities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cities' => 'required|array|min:1',
            'cities.*' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Setting::setValue('cities', json_encode($request->cities));

        return response()->json([
            'message' => 'Liste des villes mise à jour avec succès',
            'cities' => $request->cities
        ]);
    }

    /**
     * Add a new city
     */
    public function addCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cities = json_decode(Setting::getValue('cities', '[]'), true);
        
        if (!in_array($request->city, $cities)) {
            $cities[] = $request->city;
            Setting::setValue('cities', json_encode($cities));
        }

        return response()->json([
            'message' => 'Ville ajoutée avec succès',
            'cities' => $cities
        ]);
    }

    /**
     * Remove a city
     */
    public function removeCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cities = json_decode(Setting::getValue('cities', '[]'), true);
        $cities = array_values(array_filter($cities, fn($city) => $city !== $request->city));
        
        Setting::setValue('cities', json_encode($cities));

        return response()->json([
            'message' => 'Ville supprimée avec succès',
            'cities' => $cities
        ]);
    }

    // =====================================================
    // FAMILLES ARTICLE
    // =====================================================

    /**
     * Get all familles article
     */
    public function getFamillesArticle()
    {
        $familles = json_decode(Setting::getValue('familles_article', '[]'), true);
        return response()->json(['familles' => $familles]);
    }

    /**
     * Add a new famille article
     */
    public function addFamilleArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $familles = json_decode(Setting::getValue('familles_article', '[]'), true);
        
        // Generate unique ID
        $id = uniqid('fam_');
        
        // Check if famille already exists
        $exists = array_filter($familles, fn($f) => strtolower($f['nom']) === strtolower($request->nom));
        if (!empty($exists)) {
            return response()->json(['message' => 'Cette famille existe déjà'], 422);
        }

        $familles[] = [
            'id' => $id,
            'nom' => $request->nom
        ];
        
        Setting::setValue('familles_article', json_encode($familles));

        return response()->json([
            'message' => 'Famille ajoutée avec succès',
            'familles' => $familles
        ]);
    }

    /**
     * Update a famille article
     */
    public function updateFamilleArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'nom' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $familles = json_decode(Setting::getValue('familles_article', '[]'), true);
        
        foreach ($familles as &$famille) {
            if ($famille['id'] === $request->id) {
                $famille['nom'] = $request->nom;
                break;
            }
        }
        
        Setting::setValue('familles_article', json_encode($familles));

        return response()->json([
            'message' => 'Famille mise à jour avec succès',
            'familles' => $familles
        ]);
    }

    /**
     * Remove a famille article
     */
    public function removeFamilleArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if famille has sous-familles
        $sousFamilles = json_decode(Setting::getValue('sous_familles_article', '[]'), true);
        $hasSousFamilles = array_filter($sousFamilles, fn($sf) => $sf['famille_id'] === $request->id);
        
        if (!empty($hasSousFamilles)) {
            return response()->json([
                'message' => 'Impossible de supprimer cette famille car elle contient des sous-familles'
            ], 422);
        }

        $familles = json_decode(Setting::getValue('familles_article', '[]'), true);
        $familles = array_values(array_filter($familles, fn($f) => $f['id'] !== $request->id));
        
        Setting::setValue('familles_article', json_encode($familles));

        return response()->json([
            'message' => 'Famille supprimée avec succès',
            'familles' => $familles
        ]);
    }

    // =====================================================
    // SOUS-FAMILLES ARTICLE
    // =====================================================

    /**
     * Get all sous-familles article
     */
    public function getSousFamillesArticle()
    {
        $sousFamilles = json_decode(Setting::getValue('sous_familles_article', '[]'), true);
        return response()->json(['sous_familles' => $sousFamilles]);
    }

    /**
     * Add a new sous-famille article
     */
    public function addSousFamilleArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'famille_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify famille exists
        $familles = json_decode(Setting::getValue('familles_article', '[]'), true);
        $familleExists = array_filter($familles, fn($f) => $f['id'] === $request->famille_id);
        
        if (empty($familleExists)) {
            return response()->json(['message' => 'La famille sélectionnée n\'existe pas'], 422);
        }

        $sousFamilles = json_decode(Setting::getValue('sous_familles_article', '[]'), true);
        
        // Generate unique ID
        $id = uniqid('sfam_');
        
        // Check if sous-famille already exists in the same famille
        $exists = array_filter($sousFamilles, fn($sf) => 
            strtolower($sf['nom']) === strtolower($request->nom) && 
            $sf['famille_id'] === $request->famille_id
        );
        if (!empty($exists)) {
            return response()->json(['message' => 'Cette sous-famille existe déjà dans cette famille'], 422);
        }

        $sousFamilles[] = [
            'id' => $id,
            'nom' => $request->nom,
            'famille_id' => $request->famille_id
        ];
        
        Setting::setValue('sous_familles_article', json_encode($sousFamilles));

        return response()->json([
            'message' => 'Sous-famille ajoutée avec succès',
            'sous_familles' => $sousFamilles
        ]);
    }

    /**
     * Update a sous-famille article
     */
    public function updateSousFamilleArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'nom' => 'required|string|max:255',
            'famille_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sousFamilles = json_decode(Setting::getValue('sous_familles_article', '[]'), true);
        
        foreach ($sousFamilles as &$sousFamille) {
            if ($sousFamille['id'] === $request->id) {
                $sousFamille['nom'] = $request->nom;
                $sousFamille['famille_id'] = $request->famille_id;
                break;
            }
        }
        
        Setting::setValue('sous_familles_article', json_encode($sousFamilles));

        return response()->json([
            'message' => 'Sous-famille mise à jour avec succès',
            'sous_familles' => $sousFamilles
        ]);
    }

    /**
     * Remove a sous-famille article
     */
    public function removeSousFamilleArticle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sousFamilles = json_decode(Setting::getValue('sous_familles_article', '[]'), true);
        $sousFamilles = array_values(array_filter($sousFamilles, fn($sf) => $sf['id'] !== $request->id));
        
        Setting::setValue('sous_familles_article', json_encode($sousFamilles));

        return response()->json([
            'message' => 'Sous-famille supprimée avec succès',
            'sous_familles' => $sousFamilles
        ]);
    }

    // =====================================================
    // UNITES DE MESURE
    // =====================================================

    /**
     * Get all unités de mesure
     */
    public function getUnitesMesure()
    {
        $unites = json_decode(Setting::getValue('unites_mesure', '[]'), true);
        return response()->json(['unites' => $unites]);
    }

    /**
     * Add a new unité de mesure
     */
    public function addUniteMesure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'abreviation' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unites = json_decode(Setting::getValue('unites_mesure', '[]'), true);
        
        // Generate unique ID
        $id = uniqid('unit_');
        
        // Check if unité already exists
        $exists = array_filter($unites, fn($u) => 
            strtolower($u['nom']) === strtolower($request->nom) ||
            strtolower($u['abreviation']) === strtolower($request->abreviation)
        );
        if (!empty($exists)) {
            return response()->json(['message' => 'Cette unité de mesure existe déjà'], 422);
        }

        $unites[] = [
            'id' => $id,
            'nom' => $request->nom,
            'abreviation' => $request->abreviation
        ];
        
        Setting::setValue('unites_mesure', json_encode($unites));

        return response()->json([
            'message' => 'Unité de mesure ajoutée avec succès',
            'unites' => $unites
        ]);
    }

    /**
     * Update a unité de mesure
     */
    public function updateUniteMesure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'nom' => 'required|string|max:255',
            'abreviation' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unites = json_decode(Setting::getValue('unites_mesure', '[]'), true);
        
        foreach ($unites as &$unite) {
            if ($unite['id'] === $request->id) {
                $unite['nom'] = $request->nom;
                $unite['abreviation'] = $request->abreviation;
                break;
            }
        }
        
        Setting::setValue('unites_mesure', json_encode($unites));

        return response()->json([
            'message' => 'Unité de mesure mise à jour avec succès',
            'unites' => $unites
        ]);
    }

    /**
     * Remove a unité de mesure
     */
    public function removeUniteMesure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unites = json_decode(Setting::getValue('unites_mesure', '[]'), true);
        $unites = array_values(array_filter($unites, fn($u) => $u['id'] !== $request->id));
        
        Setting::setValue('unites_mesure', json_encode($unites));

        return response()->json([
            'message' => 'Unité de mesure supprimée avec succès',
            'unites' => $unites
        ]);
    }

    // =====================================================
    // COMMERCIALES
    // =====================================================

    /**
     * Get all commerciales
     */
    public function getCommerciales()
    {
        $commerciales = json_decode(Setting::getValue('commerciales', '[]'), true);
        return response()->json(['commerciales' => $commerciales]);
    }

    /**
     * Add a new commerciale
     */
    public function addCommerciale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_commercial' => 'required|string|max:255',
            'nom_commercial' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $commerciales = json_decode(Setting::getValue('commerciales', '[]'), true);
        
        // Check if code commercial already exists
        $exists = array_filter($commerciales, fn($c) => isset($c['code_commercial']) && strtolower($c['code_commercial']) === strtolower($request->code_commercial));
        if (!empty($exists)) {
            return response()->json(['message' => 'Ce code commercial existe déjà'], 422);
        }

        // Generate unique ID
        $id = uniqid('com_');
        
        $commerciale = [
            'id' => $id,
            'code_commercial' => $request->code_commercial,
            'nom_commercial' => $request->nom_commercial
        ];

        $commerciales[] = $commerciale;
        Setting::setValue('commerciales', json_encode($commerciales));

        return response()->json([
            'message' => 'Commercial ajouté avec succès',
            'commerciales' => $commerciales
        ]);
    }

    /**
     * Update a commerciale
     */
    public function updateCommerciale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'code_commercial' => 'required|string|max:255',
            'nom_commercial' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $commerciales = json_decode(Setting::getValue('commerciales', '[]'), true);
        
        // Check if code commercial already exists for another commerciale
        $exists = array_filter($commerciales, fn($c) => 
            isset($c['id']) && $c['id'] !== $request->id && 
            isset($c['code_commercial']) && strtolower($c['code_commercial']) === strtolower($request->code_commercial)
        );
        if (!empty($exists)) {
            return response()->json(['message' => 'Ce code commercial existe déjà'], 422);
        }

        foreach ($commerciales as &$commerciale) {
            if (isset($commerciale['id']) && $commerciale['id'] === $request->id) {
                $commerciale['code_commercial'] = $request->code_commercial;
                $commerciale['nom_commercial'] = $request->nom_commercial;
                break;
            }
        }
        
        Setting::setValue('commerciales', json_encode($commerciales));

        return response()->json([
            'message' => 'Commercial mis à jour avec succès',
            'commerciales' => $commerciales
        ]);
    }

    /**
     * Remove a commerciale
     */
    public function removeCommerciale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $commerciales = json_decode(Setting::getValue('commerciales', '[]'), true);
        $commerciales = array_values(array_filter($commerciales, fn($c) => !isset($c['id']) || $c['id'] !== $request->id));
        
        Setting::setValue('commerciales', json_encode($commerciales));

        return response()->json([
            'message' => 'Commercial supprimé avec succès',
            'commerciales' => $commerciales
        ]);
    }

    // =====================================================
    // TRANSPORTS
    // =====================================================

    /**
     * Get all transports
     */
    public function getTransports()
    {
        $transports = json_decode(Setting::getValue('transports', '[]'), true);
        return response()->json(['transports' => $transports]);
    }

    /**
     * Add a new transport
     */
    public function addTransport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:120',
            'telephone' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transports = json_decode(Setting::getValue('transports', '[]'), true);
        
        // Generate unique ID
        $id = uniqid('trans_');
        
        $transport = [
            'id' => $id,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'age' => $request->age,
            'telephone' => $request->telephone
        ];

        $transports[] = $transport;
        Setting::setValue('transports', json_encode($transports));

        return response()->json([
            'message' => 'Transport ajouté avec succès',
            'transports' => $transports
        ]);
    }

    /**
     * Update a transport
     */
    public function updateTransport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:120',
            'telephone' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transports = json_decode(Setting::getValue('transports', '[]'), true);
        
        foreach ($transports as &$transport) {
            if (isset($transport['id']) && $transport['id'] === $request->id) {
                $transport['nom'] = $request->nom;
                $transport['prenom'] = $request->prenom;
                $transport['age'] = $request->age;
                $transport['telephone'] = $request->telephone;
                break;
            }
        }
        
        Setting::setValue('transports', json_encode($transports));

        return response()->json([
            'message' => 'Transport mis à jour avec succès',
            'transports' => $transports
        ]);
    }

    /**
     * Remove a transport
     */
    public function removeTransport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transports = json_decode(Setting::getValue('transports', '[]'), true);
        $transports = array_values(array_filter($transports, fn($t) => !isset($t['id']) || $t['id'] !== $request->id));
        
        Setting::setValue('transports', json_encode($transports));

        return response()->json([
            'message' => 'Transport supprimé avec succès',
            'transports' => $transports
        ]);
    }

    // =====================================================
    // MATRICULES
    // =====================================================

    /**
     * Get all matricules
     */
    public function getMatricules()
    {
        $matricules = json_decode(Setting::getValue('matricules', '[]'), true);
        return response()->json(['matricules' => $matricules]);
    }

    /**
     * Add a new matricule
     */
    public function addMatricule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicule_name' => 'required|string|max:255',
            'matricule' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $matricules = json_decode(Setting::getValue('matricules', '[]'), true);
        
        // Check if matricule already exists
        $exists = array_filter($matricules, fn($m) => isset($m['matricule']) && strtolower($m['matricule']) === strtolower($request->matricule));
        if (!empty($exists)) {
            return response()->json(['message' => 'Ce matricule existe déjà'], 422);
        }

        // Generate unique ID
        $id = uniqid('mat_');
        
        $matricule = [
            'id' => $id,
            'vehicule_name' => $request->vehicule_name,
            'matricule' => $request->matricule
        ];

        $matricules[] = $matricule;
        Setting::setValue('matricules', json_encode($matricules));

        return response()->json([
            'message' => 'Matricule ajouté avec succès',
            'matricules' => $matricules
        ]);
    }

    /**
     * Update a matricule
     */
    public function updateMatricule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'vehicule_name' => 'required|string|max:255',
            'matricule' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $matricules = json_decode(Setting::getValue('matricules', '[]'), true);
        
        // Check if matricule already exists for another vehicule
        $exists = array_filter($matricules, fn($m) => 
            isset($m['id']) && $m['id'] !== $request->id && 
            isset($m['matricule']) && strtolower($m['matricule']) === strtolower($request->matricule)
        );
        if (!empty($exists)) {
            return response()->json(['message' => 'Ce matricule existe déjà'], 422);
        }
        
        foreach ($matricules as &$matricule) {
            if (isset($matricule['id']) && $matricule['id'] === $request->id) {
                $matricule['vehicule_name'] = $request->vehicule_name;
                $matricule['matricule'] = $request->matricule;
                break;
            }
        }
        
        Setting::setValue('matricules', json_encode($matricules));

        return response()->json([
            'message' => 'Matricule mis à jour avec succès',
            'matricules' => $matricules
        ]);
    }

    /**
     * Remove a matricule
     */
    public function removeMatricule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $matricules = json_decode(Setting::getValue('matricules', '[]'), true);
        $matricules = array_values(array_filter($matricules, fn($m) => !isset($m['id']) || $m['id'] !== $request->id));
        
        Setting::setValue('matricules', json_encode($matricules));

        return response()->json([
            'message' => 'Matricule supprimé avec succès',
            'matricules' => $matricules
        ]);
    }

    // =====================================================
    // BANQUES
    // =====================================================

    /**
     * Get all banques
     */
    public function getBanques()
    {
        $banques = \App\Models\Banque::orderBy('nom')->get();
        return response()->json(['banques' => $banques]);
    }

    /**
     * Add a new banque
     */
    public function addBanque(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:banques,nom'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'message' => 'Cette banque existe déjà'], 422);
        }

        $banque = \App\Models\Banque::create([
            'nom' => $request->nom
        ]);

        $banques = \App\Models\Banque::orderBy('nom')->get();

        return response()->json([
            'message' => 'Banque ajoutée avec succès',
            'banques' => $banques
        ]);
    }

    /**
     * Update a banque
     */
    public function updateBanque(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:banques,id',
            'nom' => 'required|string|max:255|unique:banques,nom,' . $request->id
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'message' => 'Cette banque existe déjà'], 422);
        }

        $banque = \App\Models\Banque::findOrFail($request->id);
        $banque->update(['nom' => $request->nom]);

        $banques = \App\Models\Banque::orderBy('nom')->get();

        return response()->json([
            'message' => 'Banque mise à jour avec succès',
            'banques' => $banques
        ]);
    }

    /**
     * Remove a banque
     */
    public function removeBanque(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:banques,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $banque = \App\Models\Banque::findOrFail($request->id);
        $banque->delete();

        $banques = \App\Models\Banque::orderBy('nom')->get();

        return response()->json([
            'message' => 'Banque supprimée avec succès',
            'banques' => $banques
        ]);
    }
}
