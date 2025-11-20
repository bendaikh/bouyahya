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
            'cities' => json_decode(Setting::getValue('cities', '[]'), true)
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
}
