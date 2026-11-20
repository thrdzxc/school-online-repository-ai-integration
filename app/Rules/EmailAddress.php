<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailAddress implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $fail('Enter a complete email address, such as student@gmail.com.');
        }
    }
}
