<?php

namespace App\Actions\Admin\System;

use App\Models\SystemSetting;
use Illuminate\Validation\ValidationException;

class SaveSystemSettingAction
{
    public function handle(string $key, mixed $value): void
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();

        $value = match ($setting->type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) intval($value),
            'decimal' => (string) round((float) str_replace(',', '.', $value), 2),
            default   => trim((string) $value),
        };

        if ($setting->type === 'integer' && ! is_numeric($value)) {
            throw ValidationException::withMessages([$key => 'O valor deve ser um número inteiro.']);
        }

        if ($setting->type === 'decimal' && ! is_numeric($value)) {
            throw ValidationException::withMessages([$key => 'O valor deve ser um número decimal.']);
        }

        SystemSetting::set($key, $value);
    }
}
