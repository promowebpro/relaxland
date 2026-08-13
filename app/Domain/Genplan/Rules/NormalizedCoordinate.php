<?php

namespace App\Domain\Genplan\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NormalizedCoordinate implements ValidationRule
{
    public function __construct(private readonly bool $nullable = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->nullable && ($value === null || $value === '')) {
            return;
        }

        if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0 || (float) $value > 1) {
            $fail('Координата должна находиться в диапазоне от 0 до 1.');
        }
    }
}
