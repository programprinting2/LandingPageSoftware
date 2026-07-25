<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationPackage extends Model
{
    protected $fillable = [
        'application_id',
        'name',
        'devices_quota',
        'duration_days',
        'price',
        'discount_percent',
        'affiliate_commission_fixed',
        'affiliate_commission_percent',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'devices_quota' => 'integer',
            'duration_days' => 'integer',
            'price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'affiliate_commission_fixed' => 'decimal:2',
            'affiliate_commission_percent' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function calculateAffiliateCommission(float $saleAmount): float
    {
        $percent = (float) $this->affiliate_commission_percent;
        $fixed = (float) $this->affiliate_commission_fixed;

        if ($percent > 0) {
            return round($saleAmount * ($percent / 100), 2);
        }

        if ($fixed > 0) {
            return round($fixed, 2);
        }

        return 0.0;
    }

    public function affiliateCommissionLabel(float $saleAmount): string
    {
        $percent = (float) $this->affiliate_commission_percent;
        $fixed = (float) $this->affiliate_commission_fixed;

        if ($percent > 0) {
            $amount = $this->calculateAffiliateCommission($saleAmount);

            return number_format($percent, 0, ',', '.').'% (Rp '.number_format($amount, 0, ',', '.').')';
        }

        if ($fixed > 0) {
            return 'Rp '.number_format($fixed, 0, ',', '.');
        }

        return '—';
    }
}
