<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $row = static::query()->where('key', $key)->first();
        return $row?->value ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getSecret(string $key): ?string
    {
        $value = static::getValue($key);
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return null;
        }
    }

    public static function setSecret(string $key, ?string $value): void
    {
        static::setValue($key, filled($value) ? Crypt::encryptString(trim($value)) : null);
    }
}
