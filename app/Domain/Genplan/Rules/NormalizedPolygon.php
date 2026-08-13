<?php

namespace App\Domain\Genplan\Rules;

use App\Domain\Genplan\NormalizedGeometry;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;

class NormalizedPolygon implements ValidationRule
{
    public function __construct(private readonly bool $nullable = false) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            app(NormalizedGeometry::class)->polygon($value, $attribute, $this->nullable);
        } catch (ValidationException $exception) {
            $fail($exception->validator->errors()->first());
        }
    }
}
