<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\LicenseDevice;
use App\Models\User;
use Illuminate\Support\Carbon;

class DeviceSlotService
{
    public function billableExtraSlots(ApplicationPackage $package, int $totalDevicesTarget): int
    {
        $base = max(1, (int) $package->devices_quota);
        $total = max($base, $totalDevicesTarget);

        return max(0, $total - $base);
    }

    public function maxTotalDevices(ApplicationPackage $package): int
    {
        $base = max(1, (int) $package->devices_quota);
        $maxExtra = max(0, (int) $package->max_extra_device_slots);

        return $base + $maxExtra;
    }

    public function effectiveDevicesMax(?ApplicationPackage $package, int $extraDeviceSlots): int
    {
        if (! $package) {
            return max(0, $extraDeviceSlots);
        }

        $base = max(1, (int) $package->devices_quota);

        return $base + max(0, $extraDeviceSlots);
    }

    /**
     * @return array{status: string, message?: string, extra_slots?: int, total_devices?: int}
     */
    public function validateTotalDevicesForCheckout(ApplicationPackage $package, int $totalDevicesTarget): array
    {
        $base = max(1, (int) $package->devices_quota);
        $maxTotal = $this->maxTotalDevices($package);
        $total = (int) $totalDevicesTarget;

        if ($total < $base) {
            return [
                'status' => 'error',
                'message' => "Jumlah perangkat minimal {$base} (limit default paket).",
            ];
        }

        if ($total > $maxTotal) {
            return [
                'status' => 'error',
                'message' => "Maksimal {$maxTotal} perangkat untuk paket ini ({$base} default + ".max(0, (int) $package->max_extra_device_slots).' slot tambahan).',
            ];
        }

        return [
            'status' => 'success',
            'extra_slots' => $this->billableExtraSlots($package, $total),
            'total_devices' => $total,
        ];
    }

    /**
     * @return array{status: string, message?: string, additional_slots?: int}
     */
    public function validateAdditionalSlotsPurchase(
        User $user,
        Application $application,
        ApplicationPackage $activePackage,
        int $additionalSlots,
    ): array {
        $additionalSlots = (int) $additionalSlots;

        if ($additionalSlots < 1) {
            return [
                'status' => 'error',
                'message' => 'Pilih minimal 1 slot tambahan.',
            ];
        }

        if ($activePackage->is_trial || $this->isTrialLikePackage($activePackage)) {
            return [
                'status' => 'error',
                'message' => 'Tambah perangkat tidak tersedia untuk paket trial.',
            ];
        }

        $user->loadMissing('client.applications');
        $owned = $user->client?->applications->firstWhere('id', $application->id);

        if (! $owned || ! $this->isPivotLicenseActive($owned)) {
            return [
                'status' => 'error',
                'message' => 'Lisensi tidak aktif. Perpanjang paket terlebih dahulu.',
            ];
        }

        $currentExtra = (int) ($owned->pivot->extra_device_slots ?? 0);
        $maxExtra = max(0, (int) $activePackage->max_extra_device_slots);
        $remaining = $maxExtra - $currentExtra;

        if ($remaining <= 0) {
            return [
                'status' => 'error',
                'message' => 'Kuota slot tambahan untuk paket ini sudah penuh.',
            ];
        }

        if ($additionalSlots > $remaining) {
            return [
                'status' => 'error',
                'message' => "Anda hanya dapat menambah {$remaining} slot lagi untuk paket ini.",
            ];
        }

        $slotPrice = (float) $activePackage->device_slot_price;
        if ($slotPrice <= 0 && $additionalSlots > 0) {
            return [
                'status' => 'error',
                'message' => 'Harga slot perangkat belum dikonfigurasi untuk paket ini.',
            ];
        }

        return [
            'status' => 'success',
            'additional_slots' => $additionalSlots,
        ];
    }

    public function slotLineAmount(ApplicationPackage $package, int $extraSlots): float
    {
        if ($extraSlots <= 0) {
            return 0.0;
        }

        return round((float) $package->device_slot_price * $extraSlots, 2);
    }

    public function clearRegisteredDevices(User $user, int $applicationId): int
    {
        return LicenseDevice::query()
            ->where('user_id', $user->id)
            ->where('application_id', $applicationId)
            ->delete();
    }

    public function finalizeExpiredLicense(User $user, Application $application): void
    {
        $user->loadMissing('client.applications');
        $owned = $user->client?->applications->firstWhere('id', $application->id);

        if (! $owned) {
            return;
        }

        $expiresAt = $owned->pivot->expires_at ? Carbon::parse($owned->pivot->expires_at) : null;
        $status = (string) ($owned->pivot->status ?? '');
        $isExpired = ($expiresAt && now()->greaterThan($expiresAt)) || $status === 'expired';

        if (! $isExpired) {
            return;
        }

        $this->clearRegisteredDevices($user, $application->id);

        $updates = [
            'extra_device_slots' => 0,
            'updated_at' => now(),
        ];

        if ($expiresAt && now()->greaterThan($expiresAt) && $status !== 'expired') {
            $updates['status'] = 'expired';
        }

        $package = $owned->pivot->application_package_id
            ? ApplicationPackage::find($owned->pivot->application_package_id)
            : null;
        $base = $package ? max(1, (int) $package->devices_quota) : (int) ($owned->pivot->devices_max ?? 1);
        $updates['devices_max'] = $base;

        $user->client->applications()->updateExistingPivot($application->id, $updates);
    }

    public function isTrialLikePackage(ApplicationPackage $package): bool
    {
        return (bool) $package->is_trial
            || ((float) $package->price <= 0 && preg_match('/trial/i', $package->name));
    }

    private function isPivotLicenseActive(mixed $owned): bool
    {
        $status = (string) ($owned->pivot->status ?? '');
        if (! in_array($status, ['active', 'trial'], true)) {
            return false;
        }

        $expiresAt = $owned->pivot->expires_at ? Carbon::parse($owned->pivot->expires_at) : null;

        return ! ($expiresAt && now()->greaterThan($expiresAt));
    }
}
