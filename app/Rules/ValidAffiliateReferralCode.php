<?php

namespace App\Rules;

use App\Support\AffiliateReferralCode;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidAffiliateReferralCode implements ValidationRule
{
    public function __construct(
        private ?int $ignoreAffiliateId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $result = AffiliateReferralCode::validate($value, $this->ignoreAffiliateId);

        if (! $result['ok']) {
            $fail($result['message'] ?? 'Kode tidak valid.');
        }
    }
}
