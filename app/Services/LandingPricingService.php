<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationPackage;
class LandingPricingService
{
    /**
     * @param  array{badge?: string, title?: string, subtitle?: string, items?: array<int, array<string, mixed>>, package_overrides?: array<int, array<string, mixed>>}  $pricingSection
     * @return array<int, array<string, mixed>>
     */
    public function buildPlans(Application $application, array $pricingSection): array
    {
        $packages = $application->packages()->orderBy('sort_order')->orderBy('id')->get();
        if ($packages->isEmpty()) {
            return $this->legacyItems($pricingSection);
        }

        $legacyItems = collect($pricingSection['items'] ?? []);
        $overrides = collect($pricingSection['package_overrides'] ?? [])
            ->keyBy(fn ($row) => (int) ($row['package_id'] ?? 0));

        return $packages->values()->map(function (ApplicationPackage $package, int $index) use ($legacyItems, $overrides) {
            $override = $overrides->get($package->id, []);
            $legacy = $legacyItems->get($index, []);

            $features = $override['features'] ?? $legacy['features'] ?? $this->defaultFeatures($package);
            if (! is_array($features)) {
                $features = [];
            }

            return [
                'package_id' => $package->id,
                'name' => $package->name,
                'is_trial' => (bool) $package->is_trial
                    || ((float) $package->price <= 0 && preg_match('/trial/i', $package->name)),
                'price' => $this->formatPrice($package),
                'price_amount' => (float) $package->price,
                'period' => $this->formatPeriod($package, (float) $package->price),
                'features' => array_values(array_filter(array_map('strval', $features))),
                'popular' => (bool) ($override['popular'] ?? $legacy['popular'] ?? false),
            ];
        })->all();
    }

    /**
     * @param  array{badge?: string, title?: string, subtitle?: string, items?: array<int, array<string, mixed>>}  $pricingSection
     * @return array<int, array<string, mixed>>
     */
    private function legacyItems(array $pricingSection): array
    {
        $items = $pricingSection['items'] ?? [];

        return is_array($items) ? array_values($items) : [];
    }

    private function formatPrice(ApplicationPackage $package): string
    {
        $amount = (float) $package->price;
        if ($amount <= 0) {
            return 'Gratis';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function formatPeriod(ApplicationPackage $package, float $priceAmount): string
    {
        $days = (int) $package->duration_days;
        $isFree = $priceAmount <= 0;

        if ($isFree) {
            if ($days === 0) {
                return 'Unlimited';
            }

            return $days.' hari';
        }

        if ($days === 0) {
            return '/ unlimited';
        }
        if ($days === 365) {
            return '/tahun';
        }
        if ($days === 30) {
            return '/bulan';
        }

        return '/'.$days.' hari';
    }

    /**
     * @return array<int, string>
     */
    private function defaultFeatures(ApplicationPackage $package): array
    {
        $features = [];
        $quota = (int) $package->devices_quota;
        if ($quota > 0) {
            $features[] = $quota === 1 ? '1 Perangkat' : $quota.' Perangkat';
        }

        return $features;
    }
}
