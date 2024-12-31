<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_KEY = 'settings_';
    private const CACHE_TTL = 86400; // 24 saat

    /**
     * Ayarı getir
     */
    public function get(string|SettingKey $key, mixed $default = null): mixed
    {
        if ($key instanceof SettingKey) {
            $key = $key->value;
        }

        $cacheKey = $this->getCacheKey($key);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Tüm ayarları getir
     */
    public function all(): array
    {
        $cacheKey = $this->getCacheKey('all');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $settings = Setting::all()->pluck('value', 'key')->toArray();
            $defaults = $this->getDefaults();
            
            return array_merge($defaults, $settings);
        });
    }

    /**
     * Ayarı güncelle
     */
    public function set(string|SettingKey $key, mixed $value): void
    {
        if ($key instanceof SettingKey) {
            $key = $key->value;
        }

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        $this->clearCache($key);
    }

    /**
     * Toplu güncelleme
     */
    public function bulkSet(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }

        $this->clearCache('all');
    }

    /**
     * Cache'i temizle
     */
    private function clearCache(string $key): void
    {
        Cache::forget($this->getCacheKey($key));
        Cache::forget($this->getCacheKey('all'));
    }

    /**
     * Cache key oluştur
     */
    private function getCacheKey(string $key): string
    {
        $tenantId = request()->user() && app()->has('currentTenant') ? app('currentTenant')->id : null;
        return self::CACHE_KEY . $tenantId . '_' . $key;
    }

    /**
     * Varsayılan değerleri getir
     */
    private function getDefaults(): array
    {
        $defaults = [];
        foreach (SettingKey::getGroups() as $group) {
            foreach ($group['settings'] as $key => $setting) {
                $defaults[$key] = $setting['default'];
            }
        }
        return $defaults;
    }
} 