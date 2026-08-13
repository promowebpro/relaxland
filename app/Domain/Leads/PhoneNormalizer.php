<?php

namespace App\Domain\Leads;

class PhoneNormalizer
{
    public function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';

        if (strlen($digits) === 10) {
            return '+7'.$digits;
        }

        if (strlen($digits) === 11 && $digits[0] === '8') {
            return '+7'.substr($digits, 1);
        }

        if (strlen($digits) === 11 && $digits[0] === '7') {
            return '+'.$digits;
        }

        return str_starts_with(trim($phone), '+') ? '+'.$digits : $digits;
    }
}
