<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Application;
use App\Models\PromoVoucher;
use App\Support\AffiliateReferralCode;

class CheckoutReferralService
{
    /**
     * @return array{
     *   type: 'affiliate'|'promo',
     *   code: string,
     *   affiliate: ?Affiliate,
     *   promo_voucher: ?PromoVoucher,
     *   discount_percent: float
     * }|null
     */
    public function resolve(?string $code, ?Application $application = null): ?array
    {
        $normalized = AffiliateReferralCode::normalize($code);

        if ($normalized === '' || ! AffiliateReferralCode::isValidFormat($normalized)) {
            return null;
        }

        if (AffiliateReferralCode::isReserved($normalized)) {
            return null;
        }

        $affiliate = Affiliate::query()
            ->where('code', $normalized)
            ->where('status', 'active')
            ->first();

        if ($affiliate) {
            return [
                'type' => 'affiliate',
                'code' => $normalized,
                'affiliate' => $affiliate,
                'promo_voucher' => null,
                'discount_percent' => 0.0,
            ];
        }

        $promo = PromoVoucher::query()
            ->where('code', $normalized)
            ->where('status', 'active')
            ->first();

        if (! $promo) {
            return null;
        }

        if ($promo->application_id !== null && $application !== null && (int) $promo->application_id !== (int) $application->id) {
            return null;
        }

        return [
            'type' => 'promo',
            'code' => $normalized,
            'affiliate' => null,
            'promo_voucher' => $promo,
            'discount_percent' => max(0, min(100, (float) $promo->discount_percent)),
        ];
    }
}
