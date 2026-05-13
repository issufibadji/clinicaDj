<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class SystemSetting extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = ['key', 'value', 'type', 'label', 'description'];

    protected array $auditInclude = ['value'];

    private const CACHE_TTL = 3600; // 1 hora

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'decimal' => (float) $this->value,
            default   => $this->value,
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("system_setting.{$key}", self::CACHE_TTL, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->typed_value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => (string) $value]);
        Cache::forget("system_setting.{$key}");
    }

    public static function clearCache(): void
    {
        static::pluck('key')->each(fn($key) => Cache::forget("system_setting.{$key}"));
    }
}
