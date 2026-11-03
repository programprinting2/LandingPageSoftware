<?php

namespace App\Support;

use App\Models\PaymentOrder;

class PaymentOrderVoucherDisplay
{
    /**
     * @return array{voucher_code: ?string, voucher_kind: ?string, voucher_source_label: ?string}
     */
    public static function fromOrder(?PaymentOrder $order): array
    {
        if (! $order || ! $order->referral_code) {
            return [
                'voucher_code' => null,
                'voucher_kind' => null,
                'voucher_source_label' => null,
            ];
        }

        $code = (string) $order->referral_code;

        if ($order->promo_voucher_id) {
            return [
                'voucher_code' => $code,
                'voucher_kind' => 'promo',
                'voucher_source_label' => 'Promo admin',
            ];
        }

        if ($order->affiliate_id) {
            $order->loadMissing('affiliate.user:id,name');
            $name = $order->affiliate?->user?->name;

            return [
                'voucher_code' => $code,
                'voucher_kind' => 'affiliate',
                'voucher_source_label' => $name ? "Affiliate · {$name}" : 'Affiliate',
            ];
        }

        return [
            'voucher_code' => $code,
            'voucher_kind' => 'unknown',
            'voucher_source_label' => null,
        ];
    }
}
