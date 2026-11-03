<?php

namespace App\Rules;

use App\Support\AffiliateReferralCode;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPromoVoucherCode implements ValidationRule
{
    public function __construct(
        private ?int $ignorePromoVoucherId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = AffiliateReferralCode::validate($value, null, $this->ignorePromoVoucherId);

        if (! $result['ok']) {
            $fail($result['message'] ?? 'Kode tidak valid.');
        }
    }
}
