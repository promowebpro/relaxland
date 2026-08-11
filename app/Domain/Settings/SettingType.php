<?php

namespace App\Domain\Settings;

enum SettingType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';
    case Array = 'array';

    public static function fromValue(mixed $value): self
    {
        return match (true) {
            is_bool($value) => self::Boolean,
            is_int($value) => self::Integer,
            is_float($value) => self::Float,
            is_array($value) => self::Array,
            default => self::String,
        };
    }
}
