<?php

namespace App\Support;

use App\Rules\PersonName;

class AccountValidation
{
    public static function name(): array
    {
        return ['required', 'string', 'max:150', new PersonName()];
    }

    public static function password(bool $confirmed = false): array
    {
        $rules = [
            'required', 'string', 'min:8', 'max:255',
            'regex:/[A-Z]/', // At least one uppercase letter.
            'regex:/[^A-Za-z0-9]/', // At least one special character.
        ];

        if ($confirmed) $rules[] = 'confirmed';
        return $rules;
    }

    public static function normalizeNameAndEmail(\Illuminate\Http\Request $request): void
    {
        $fields = [];
        if ($request->exists('name')) {
            $fields['name'] = preg_replace('/\s+/u', ' ', trim((string) $request->input('name')));
        }
        if ($request->exists('email')) {
            $fields['email'] = strtolower(trim((string) $request->input('email')));
        }
        if ($fields) $request->merge($fields);
    }
}
