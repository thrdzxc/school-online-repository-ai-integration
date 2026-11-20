<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AcademicName implements ValidationRule
{
    public function __construct(private string $label, private bool $allowNumbers = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pattern = $this->allowNumbers
            ? '/\A[\p{L}0-9][\p{L}\p{M}0-9 ]*\z/u'
            : '/\A\p{L}[\p{L}\p{M} ]*\z/u';
        if (!is_string($value) || !preg_match($pattern, $value)) {
            $fail($this->label.' may contain only '.($this->allowNumbers ? 'letters, numbers, and spaces.' : 'letters and spaces.'));
        }
    }
}
