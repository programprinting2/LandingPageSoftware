<?php

namespace App\Support;

class WhatsappNumber
{
    /**
     * Normalize free-form phone input to local Indonesian format (08…), digits only.
     */
    public static function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            $rest = substr($digits, 2);
            $digits = $rest !== '' ? '0'.$rest : '0';
        } elseif ($digits[0] === '8') {
            $digits = '0'.$digits;
        }

        return substr($digits, 0, 20);
    }
}
