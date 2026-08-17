<?php

namespace App\Domain\Genplan;

use Illuminate\Validation\ValidationException;

final readonly class GeographicPoint
{
    private const DECIMAL_PATTERN = '/^[+-]?(?:\d+)(?:\.\d{1,7})?$/D';

    public function __construct(
        public string $latitude,
        public string $longitude,
    ) {
        self::assertCoordinate($latitude, '-90', '90', 'latitude');
        self::assertCoordinate($longitude, '-180', '180', 'longitude');
    }

    public static function tryFrom(mixed $latitude, mixed $longitude): ?self
    {
        if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
            return null;
        }

        try {
            return new self(self::stringValue($latitude), self::stringValue($longitude));
        } catch (ValidationException) {
            return null;
        }
    }

    /** @return array{latitude: float, longitude: float} */
    public function numeric(): array
    {
        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }

    private static function stringValue(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value)) {
            throw ValidationException::withMessages(['coordinates' => 'Координаты должны быть decimal-значениями.']);
        }

        return (string) $value;
    }

    private static function assertCoordinate(string $value, string $minimum, string $maximum, string $field): void
    {
        if (! preg_match(self::DECIMAL_PATTERN, $value)) {
            throw ValidationException::withMessages([$field => 'Координата должна быть конечным decimal-значением с точностью до 7 знаков.']);
        }

        if (self::compareDecimals($value, $minimum) < 0 || self::compareDecimals($value, $maximum) > 0) {
            throw ValidationException::withMessages([$field => 'Координата находится вне допустимого диапазона.']);
        }
    }

    private static function compareDecimals(string $left, string $right): int
    {
        $normalize = static function (string $value): array {
            $negative = str_starts_with($value, '-');
            $unsigned = ltrim($value, '+-');
            [$whole, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');

            return [$negative, ltrim($whole, '0') ?: '0', str_pad($fraction, 7, '0')];
        };

        [$leftNegative, $leftWhole, $leftFraction] = $normalize($left);
        [$rightNegative, $rightWhole, $rightFraction] = $normalize($right);

        if ($leftNegative !== $rightNegative) {
            return $leftNegative ? -1 : 1;
        }

        $magnitude = strlen($leftWhole) <=> strlen($rightWhole);
        $magnitude = $magnitude ?: strcmp($leftWhole, $rightWhole);
        $magnitude = $magnitude ?: strcmp($leftFraction, $rightFraction);

        return $leftNegative ? -$magnitude : $magnitude;
    }
}
