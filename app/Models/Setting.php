<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = self::getAllCached();
        
        if (!isset($settings[$key])) {
            return $default;
        }

        $setting = $settings[$key];
        
        return self::castValue($setting['value'], $setting['type']);
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            // Encrypt if type is encrypted
            if ($setting->type === 'encrypted' && $value) {
                $value = Crypt::encryptString($value);
            }
            
            $setting->update(['value' => $value]);
        }

        self::clearCache();
    }

    /**
     * Get all settings cached.
     */
    public static function getAllCached(): array
    {
        return Cache::remember('app_settings', 3600, function () {
            return self::all()->keyBy('key')->map(function ($setting) {
                return [
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'group' => $setting->group,
                ];
            })->toArray();
        });
    }

    /**
     * Get settings by group.
     */
    public static function getByGroup(string $group): array
    {
        $settings = self::getAllCached();
        
        return collect($settings)
            ->filter(fn($s) => $s['group'] === $group)
            ->map(fn($s) => self::castValue($s['value'], $s['type']))
            ->toArray();
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('app_settings');
    }

    /**
     * Cast value based on type.
     */
    protected static function castValue(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'encrypted' => self::decryptValue($value),
            default => $value,
        };
    }

    /**
     * Decrypt encrypted value.
     */
    protected static function decryptValue(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
