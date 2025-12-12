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
            'commerciales' => json_decode(Setting::getValue('commerciales', '[]'), true)
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
}
