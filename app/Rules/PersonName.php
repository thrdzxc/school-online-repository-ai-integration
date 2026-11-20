<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PersonName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Accept Unicode names including spaces, hyphens and apostrophes, but no digits.
        if (!is_string($value) || !preg_match("/\A[\p{L}\p{M}]+(?:[ '\x{2019}-][\p{L}\p{M}]+)*\z/u", trim($value))) {
            $fail('The :attribute may contain letters, spaces, hyphens, and apostrophes only.');
        }
    }
}
