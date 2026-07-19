<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'site_settings.all';

    /**
     * All settings as a key => value array (cached).
     *
     * @return array<string, string|null>
     */
    public static function all(mixed $columns = ['*']): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    /** Get a single setting value with an optional default. */
    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();

        return $all[$key] ?? $default;
    }

    /**
     * Persist many settings at once and bust the cache.
     *
     * @param array<string, string|null> $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        // Any direct write also busts the cache.
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
