<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get the application display name from settings.
     */
    public static function getAppName(): string
    {
        return Cache::remember('settings.app_name', 86400, function () {
            return self::getValue('app_name', config('app.name', 'Bouyahya'));
        });
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        if ($key === 'app_name') {
            Cache::forget('settings.app_name');
        }
    }
}
