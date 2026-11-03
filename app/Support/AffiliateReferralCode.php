<?php

namespace App\Support;

use App\Models\Affiliate;
use App\Models\PromoVoucher;
use Illuminate\Support\Str;

class AffiliateReferralCode
{
    public const LENGTH = 6;

    /** @var list<string> */
    private const RESERVED = [
        'ADMIN', 'ADMINS', 'SYSTEM', 'SYSTMS', 'ROOT', 'SUPER', 'MASTER',
        'MODER', 'STAFF', 'SUPPORT', 'HELP', 'PUBLIC', 'PRIVATE',
        'NULL', 'VOID', 'EMPTY', 'TEST', 'DEBUG', 'DEMO', 'SAMPLE',
        'DELETE', 'REMOVE', 'UPDATE', 'INSERT', 'SELECT', 'DROP', 'TABLE',
        'TOKEN', 'SECRET', 'APIKEY', 'PASSWD', 'PASSWORD', 'LOGIN', 'LOGOUT',
        'VOUCHER', 'PROMO', 'DISKON', 'KODE', 'REFERR', 'AFFIL', 'AFFILI',
        'OWNER', 'GODMOD', 'BYPASS', 'ENABLE', 'DISABLE', 'SPOTCO', 'QUICKS',
    ];

    public static function normalize(mixed $value): string
    {
        $raw = Str::upper(Str::ascii(trim((string) $value)));

        return preg_replace('/[^A-Z0-9]/', '', $raw) ?? '';
    }

    public static function isValidFormat(string $code): bool
    {
        return (bool) preg_match('/^[A-Z0-9]{'.self::LENGTH.'}$/', $code);
    }

    public static function isReserved(string $code): bool
    {
        $normalized = self::normalize($code);

        if ($normalized === '') {
            return false;
        }

        if (in_array($normalized, self::RESERVED, true)) {
            return true;
        }

        foreach (self::RESERVED as $word) {
            if (strlen($word) <= self::LENGTH && str_starts_with($normalized, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{ok: bool, code: string, message: ?string}
     */
    public static function validate(mixed $value, ?int $ignoreAffiliateId = null, ?int $ignorePromoVoucherId = null): array
    {
        $code = self::normalize($value);

        if ($code === '') {
            return ['ok' => false, 'code' => '', 'message' => 'Kode wajib diisi.'];
        }

        if (strlen($code) !== self::LENGTH) {
            return [
                'ok' => false,
                'code' => $code,
                'message' => 'Kode harus tepat 6 karakter huruf besar dan angka.',
            ];
        }

        if (! self::isValidFormat($code)) {
            return [
                'ok' => false,
                'code' => $code,
                'message' => 'Kode hanya boleh huruf A–Z dan angka 0–9.',
            ];
        }

        if (self::isReserved($code)) {
            return [
                'ok' => false,
                'code' => $code,
                'message' => 'Kode tidak dapat digunakan. Pilih kode lain.',
            ];
        }

        $affiliateExists = Affiliate::query()
            ->where('code', $code)
            ->when($ignoreAffiliateId, fn ($q) => $q->where('id', '!=', $ignoreAffiliateId))
            ->exists();

        $promoExists = PromoVoucher::query()
            ->where('code', $code)
            ->when($ignorePromoVoucherId, fn ($q) => $q->where('id', '!=', $ignorePromoVoucherId))
            ->exists();

        if ($affiliateExists || $promoExists) {
            return [
                'ok' => false,
                'code' => $code,
                'message' => $affiliateExists && $promoExists
                    ? 'Kode sudah dipakai.'
                    : ($affiliateExists ? 'Kode sudah dipakai affiliator lain.' : 'Kode sudah dipakai voucher promo.'),
            ];
        }

        return ['ok' => true, 'code' => $code, 'message' => null];
    }
}
