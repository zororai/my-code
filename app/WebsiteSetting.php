<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'order'
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $setting = Cache::remember("website_setting_{$key}", 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set($key, $value)
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("website_setting_{$key}");
        Cache::forget('website_settings_all');

        return true;
    }

    /**
     * Get all settings grouped
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllGrouped()
    {
        return Cache::remember('website_settings_all', 3600, function () {
            return self::orderBy('group')->orderBy('order')->get()->groupBy('group');
        });
    }

    /**
     * Get settings by group
     *
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByGroup($group)
    {
        return self::where('group', $group)->orderBy('order')->get();
    }

    /**
     * Clear all website settings cache
     */
    public static function clearCache()
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("website_setting_{$setting->key}");
        }
        Cache::forget('website_settings_all');
    }
}
