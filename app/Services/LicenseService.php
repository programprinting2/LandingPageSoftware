<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\LicenseDevice;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LicenseService
{
    public function status(User $user, string $programId, string $applicationSlug = 'spot-color'): array
    {
        $programId = $this->normalizeProgramId($programId);
        $context = $this->resolveLicenseContext($user, $applicationSlug);

        if (isset($context['status']) && $context['status'] === 'error') {
            return $context;
        }

        /** @var Application $application */
        $application = $context['application'];
        $devicesMax = $context['devices_max'];

        $devices = LicenseDevice::where('user_id', $user->id)
            ->where('application_id', $application->id)
            ->get();
        $thisDevice = $devices->firstWhere('program_id', $programId);
        $devicesActive = $devices->count();
        $thisDeviceActive = $programId !== '' && $thisDevice !== null;

        return array_merge([
            'email' => $user->email,
            'application' => $this->applicationPayload($application),
            'devices_active' => $devicesActive,
            'devices_max' => $devicesMax,
            'this_device_active' => $thisDeviceActive,
            'licensed' => $devicesActive <= $devicesMax,
            'access_info' => $thisDevice?->access_info,
            'source' => 'server',
        ], $this->licenseInfoPayload($user, $application), $this->serverTimePayload());
    }

    public function activate(User $user, string $programId, ?array $accessInfo = null, string $applicationSlug = 'spot-color'): array
    {
        $programId = $this->normalizeProgramId($programId);

        if ($programId === '') {
            return [
                'status' => 'error',
                'message' => 'Program ID tidak valid',
            ];
        }

        $context = $this->resolveLicenseContext($user, $applicationSlug);

        if (isset($context['status']) && $context['status'] === 'error') {
            return $context;
        }

        /** @var Application $application */
        $application = $context['application'];
        $devicesMax = $context['devices_max'];

        $existing = LicenseDevice::where('user_id', $user->id)
            ->where('application_id', $application->id)
            ->where('program_id', $programId)
            ->first();

        if ($existing) {
            return array_merge(
                ['status' => 'success', 'message' => 'Perangkat sudah aktif'],
                $this->status($user, $programId, $applicationSlug)
            );
        }

        $devicesActive = LicenseDevice::where('user_id', $user->id)
            ->where('application_id', $application->id)
            ->count();

        if ($devicesActive >= $devicesMax) {
            return [
                'status' => 'error',
                'message' => 'Kuota perangkat penuh',
                'devices_active' => $devicesActive,
                'devices_max' => $devicesMax,
            ];
        }

        LicenseDevice::create([
            'user_id' => $user->id,
            'application_id' => $application->id,
            'program_id' => $programId,
            'access_info' => $accessInfo ?: null,
            'activated_at' => now(),
        ]);

        return array_merge(
            ['status' => 'success', 'message' => 'Aktivasi berhasil'],
            $this->status($user, $programId, $applicationSlug)
        );
    }

    public function deactivate(User $user, string $programId, string $applicationSlug = 'spot-color'): array
    {
        $programId = $this->normalizeProgramId($programId);

        if ($programId === '') {
            return [
                'status' => 'error',
                'message' => 'Program ID tidak valid',
            ];
        }

        $context = $this->resolveLicenseContext($user, $applicationSlug, false);

        if (isset($context['status']) && $context['status'] === 'error') {
            return $context;
        }

        /** @var Application $application */
        $application = $context['application'];

        $deleted = LicenseDevice::where('user_id', $user->id)
            ->where('application_id', $application->id)
            ->where('program_id', $programId)
            ->delete();

        if (! $deleted) {
            return [
                'status' => 'error',
                'message' => 'Perangkat tidak ditemukan',
            ];
        }

        return array_merge(
            ['status' => 'success', 'message' => 'Deaktivasi berhasil'],
            $this->status($user, $programId, $applicationSlug)
        );
    }

    public function deactivateById(User $user, int $deviceId): array
    {
        $device = LicenseDevice::where('user_id', $user->id)
            ->where('id', $deviceId)
            ->with('application')
            ->first();

        if (! $device) {
            return [
                'status' => 'error',
                'message' => 'Perangkat tidak ditemukan',
            ];
        }

        $programId = $device->program_id;
        $applicationSlug = $device->application?->slug ?? 'spot-color';
        $device->delete();

        return array_merge(
            ['status' => 'success', 'message' => 'Deaktivasi berhasil'],
            $this->status($user, $programId, $applicationSlug)
        );
    }

    public function listDevicesPublic(User $user, string $currentProgramId = '', string $applicationSlug = 'spot-color'): array
    {
        $currentProgramId = $this->normalizeProgramId($currentProgramId);
        $context = $this->resolveLicenseContext($user, $applicationSlug, false);

        if (isset($context['status']) && $context['status'] === 'error') {
            return $context;
        }

        /** @var Application $application */
        $application = $context['application'];
        $devicesMax = $context['devices_max'];

        $devices = LicenseDevice::where('user_id', $user->id)
            ->where('application_id', $application->id)
            ->orderByDesc('activated_at')
            ->get();

        return array_merge([
            'email' => $user->email,
            'application' => $this->applicationPayload($application),
            'devices_active' => $devices->count(),
            'devices_max' => $devicesMax,
            'this_device_active' => $currentProgramId !== '' && $devices->contains(
                fn (LicenseDevice $device) => $device->program_id === $currentProgramId
            ),
            'devices' => $devices
                ->map(fn (LicenseDevice $device) => $this->publicDevicePayload($device, $currentProgramId, $application))
                ->values()
                ->all(),
        ], $this->licenseInfoPayload($user, $application), $this->serverTimePayload());
    }

    public function upgradePackage(User $user, int $packageId, string $applicationSlug = 'spot-color', ?string $referralCode = null): array
    {
        return app(PaymentService::class)->createUpgradePayment($user, $packageId, $applicationSlug, $referralCode);
    }

    public function validateUpgradeRequest(User $user, int $packageId, string $applicationSlug = 'spot-color'): array
    {
        if ($packageId <= 0) {
            return [
                'status' => 'error',
                'message' => 'Paket upgrade wajib dipilih',
            ];
        }

        $user->loadMissing('client');

        if (! $user->client) {
            return [
                'status' => 'error',
                'message' => 'Akun client tidak ditemukan',
            ];
        }

        $application = $this->resolveApplication($applicationSlug);

        if (! $application) {
            return [
                'status' => 'error',
                'message' => 'Aplikasi tidak ditemukan atau tidak aktif',
            ];
        }

        $owned = $user->client->applications()
            ->where('applications.id', $application->id)
            ->first();

        if (! $owned) {
            return [
                'status' => 'error',
                'message' => 'Aplikasi belum dibeli untuk akun ini',
            ];
        }

        $targetPackage = ApplicationPackage::query()
            ->where('id', $packageId)
            ->where('application_id', $application->id)
            ->first();

        if (! $targetPackage) {
            return [
                'status' => 'error',
                'message' => 'Paket upgrade tidak valid',
            ];
        }

        $currentPackage = $owned->pivot->application_package_id
            ? ApplicationPackage::find($owned->pivot->application_package_id)
            : null;

        $orderedPackages = $this->orderedPackages($application);
        $currentRank = $this->packageRank($orderedPackages, $currentPackage);
        $targetRank = $this->packageRank($orderedPackages, $targetPackage);

        if ($targetRank < 0) {
            return [
                'status' => 'error',
                'message' => 'Paket upgrade tidak valid',
            ];
        }

        if ($targetRank <= $currentRank) {
            $currentName = $currentPackage?->name ?? 'saat ini';

            return [
                'status' => 'error',
                'message' => "Paket {$targetPackage->name} tidak lebih tinggi dari {$currentName}. Muat ulang data lisensi dan coba lagi.",
            ];
        }

        return [
            'status' => 'success',
            'application' => $application,
            'target_package' => $targetPackage,
            'current_package' => $currentPackage,
        ];
    }

    public function applyUpgradePackage(
        User $user,
        Application $application,
        ApplicationPackage $targetPackage,
        ?ApplicationPackage $currentPackage = null,
    ): void {
        $purchasedAt = now();
        $expiresAt = (int) $targetPackage->duration_days > 0
            ? $purchasedAt->copy()->addDays((int) $targetPackage->duration_days)
            : null;

        $user->client->applications()->updateExistingPivot($application->id, [
            'application_package_id' => $targetPackage->id,
            'devices_max' => (int) $targetPackage->devices_quota,
            'status' => 'active',
            'purchased_at' => $purchasedAt,
            'expires_at' => $expiresAt,
        ]);

        app(TransactionService::class)->recordUpgrade(
            $user,
            $application,
            $targetPackage,
            $currentPackage,
            'qris',
        );

        $user->refresh();
        $user->load('client.applications');
    }

    public function listForUser(User $user): array
    {
        $user->loadMissing('client.applications');

        $applications = $user->client?->applications()
            ->withPivot(['application_package_id', 'devices_max', 'status', 'purchased_at', 'expires_at'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get() ?? collect();

        return [
            'has_purchases' => $applications->isNotEmpty(),
            'applications' => $applications
                ->map(function (Application $application) use ($user) {
                    $license = $this->listDevicesPublic($user, '', $application->slug);
                    $package = $application->pivot->application_package_id
                        ? ApplicationPackage::find($application->pivot->application_package_id)
                        : null;

                    return [
                        'id' => $application->id,
                        'name' => $application->name,
                        'slug' => $application->slug,
                        'description' => $application->description,
                        'purchase_status' => $application->pivot->status,
                        'purchased_at' => $application->pivot->purchased_at,
                        'expires_at' => $application->pivot->expires_at,
                        'package' => $package ? [
                            'id' => $package->id,
                            'name' => $package->name,
                            'devices_quota' => (int) $package->devices_quota,
                            'duration_days' => (int) $package->duration_days,
                            'price' => (float) $package->price,
                        ] : null,
                        'devices_active' => $license['devices_active'] ?? 0,
                        'devices_max' => $license['devices_max'] ?? $application->default_devices_max,
                        'devices' => $license['devices'] ?? [],
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function resolveLicenseContext(User $user, string $applicationSlug, bool $requirePurchase = true): array
    {
        $user->loadMissing('client.applications');

        if (! $user->client) {
            return [
                'status' => 'error',
                'message' => 'Akun client tidak ditemukan',
            ];
        }

        $application = $this->resolveApplication($applicationSlug);

        if (! $application) {
            return [
                'status' => 'error',
                'message' => 'Aplikasi tidak ditemukan atau tidak aktif',
            ];
        }

        $owned = $user->client->applications->firstWhere('id', $application->id);

        if ($requirePurchase && ! $owned) {
            return [
                'status' => 'error',
                'message' => 'Aplikasi belum dibeli untuk akun ini',
            ];
        }

        if ($owned && ! in_array($owned->pivot->status, ['active', 'trial'], true)) {
            return [
                'status' => 'error',
                'message' => 'Lisensi aplikasi ini tidak aktif',
            ];
        }

        if ($owned && $owned->pivot->expires_at && now()->greaterThan($this->parsePivotDate($owned->pivot->expires_at))) {
            return [
                'status' => 'error',
                'message' => 'Lisensi aplikasi telah kedaluwarsa',
            ];
        }

        return [
            'application' => $application,
            'devices_max' => $this->devicesMaxFor($user, $application, $owned?->pivot),
        ];
    }

    private function resolveApplication(string $applicationSlug): ?Application
    {
        $slug = Str::slug(trim($applicationSlug));

        if ($slug === '') {
            $slug = 'spot-color';
        }

        return Application::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    private function devicesMaxFor(User $user, Application $application, $pivot = null): int
    {
        if ($pivot?->devices_max) {
            return (int) $pivot->devices_max;
        }

        if ($user->client?->devices_max) {
            return (int) $user->client->devices_max;
        }

        return (int) ($application->default_devices_max ?: config('spotcolor.devices_max', 3));
    }

    private function applicationPayload(Application $application): array
    {
        return [
            'id' => $application->id,
            'name' => $application->name,
            'slug' => $application->slug,
        ];
    }

    public function licenseInfoPayload(User $user, Application $application): array
    {
        $user->loadMissing('client.applications');

        $owned = $user->client?->applications->firstWhere('id', $application->id);
        $now = now();

        if (! $owned) {
            return [
                'license_type' => null,
                'package' => null,
                'purchase_status' => null,
                'purchased_at' => null,
                'expires_at' => null,
                'expires_at_label' => null,
                'is_expired' => false,
                'days_remaining' => null,
                'remaining_label' => null,
                'is_highest_tier' => false,
                'upgrade_packages' => [],
            ];
        }

        $package = $owned->pivot->application_package_id
            ? ApplicationPackage::find($owned->pivot->application_package_id)
            : null;

        $expiresAt = $this->parsePivotDate($owned->pivot->expires_at);
        $purchasedAt = $this->parsePivotDate($owned->pivot->purchased_at);
        $isExpired = $expiresAt ? $now->greaterThan($expiresAt) : false;

        $allPackages = $this->orderedPackages($application);

        $currentRank = $this->packageRank($allPackages, $package);
        $topRank = max(0, $allPackages->count() - 1);
        $isHighestTier = $package !== null && $currentRank >= $topRank;

        $upgradePackages = $allPackages
            ->values()
            ->filter(fn (ApplicationPackage $candidate, int $index) => $index > $currentRank)
            ->map(fn (ApplicationPackage $candidate) => $this->packagePayload($candidate))
            ->values()
            ->all();

        $purchaseStatus = (string) ($owned->pivot->status ?? '');
        $licenseType = $package?->name;
        if (! $licenseType && in_array($purchaseStatus, ['active', 'trial'], true)) {
            $licenseType = $purchaseStatus === 'trial' ? 'Trial' : 'Active';
        }

        return [
            'license_type' => $licenseType,
            'package' => $package ? $this->packagePayload($package) : null,
            'purchase_status' => $purchaseStatus,
            'purchased_at' => $purchasedAt?->toIso8601String(),
            'expires_at' => $expiresAt?->toIso8601String(),
            'expires_at_label' => $expiresAt ? $expiresAt->format('d M Y') : null,
            'is_expired' => $isExpired,
            'days_remaining' => ($expiresAt && ! $isExpired) ? (int) $now->diffInDays($expiresAt) : null,
            'remaining_label' => ($expiresAt && ! $isExpired) ? $this->formatRemainingLabel($expiresAt, $now) : null,
            'is_highest_tier' => $isHighestTier,
            'upgrade_packages' => $upgradePackages,
        ];
    }

    private function packagePayload(ApplicationPackage $package): array
    {
        return [
            'id' => $package->id,
            'name' => $package->name,
            'devices_quota' => (int) $package->devices_quota,
            'duration_days' => (int) $package->duration_days,
            'price' => (float) $package->price,
            'discount_percent' => (float) $package->discount_percent,
            'sort_order' => (int) $package->sort_order,
        ];
    }

    public function serverTimePayload(?Carbon $now = null): array
    {
        $now ??= now();

        return [
            'server_time' => $now->toIso8601String(),
            'server_time_unix' => $now->timestamp,
        ];
    }

    private function formatRemainingLabel(Carbon $expiresAt, Carbon $now): string
    {
        $seconds = max(0, $now->diffInSeconds($expiresAt));
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%d d, %dh : %dm', $days, $hours, $minutes);
    }

    private function orderedPackages(Application $application)
    {
        return ApplicationPackage::query()
            ->where('application_id', $application->id)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->orderBy('id')
            ->get();
    }

    private function packageRank($orderedPackages, ?ApplicationPackage $package): int
    {
        if (! $package) {
            return -1;
        }

        foreach ($orderedPackages->values() as $index => $candidate) {
            if ((int) $candidate->id === (int) $package->id) {
                return (int) $index;
            }
        }

        return -1;
    }

    private function parsePivotDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function publicDevicePayload(LicenseDevice $device, string $currentProgramId = '', ?Application $application = null): array
    {
        $access = is_array($device->access_info) ? $device->access_info : [];
        $ip = $access['localIp']
            ?? $access['local_ip']
            ?? $access['publicIp']
            ?? $access['public_ip']
            ?? null;
        $hostname = $access['hostname'] ?? $access['host'] ?? null;
        if (! $hostname && ! empty($access['user'])) {
            $hostname = (string) $access['user'];
        }
        $platform = $access['platform'] ?? null;
        $locationParts = array_filter([
            $access['city'] ?? null,
            $access['region'] ?? null,
            $access['country'] ?? null,
        ]);
        $location = $locationParts
            ? implode(', ', $locationParts).(isset($access['isp']) ? ' — '.$access['isp'] : '')
            : ($access['location'] ?? ($access['isp'] ?? null));

        $application ??= $device->application;

        return [
            'id' => $device->id,
            'application_id' => $device->application_id,
            'application_name' => $application?->name,
            'application_slug' => $application?->slug,
            'ip' => $ip ?: '—',
            'hostname' => $hostname ?: '—',
            'computer' => $hostname ?: '—',
            'platform' => $platform ?: '—',
            'location' => $location ?: '—',
            'activated_at' => $device->activated_at?->toIso8601String(),
            'is_current' => $currentProgramId !== '' && $device->program_id === $currentProgramId,
        ];
    }

    private function normalizeProgramId(string $programId): string
    {
        return Str::upper(trim($programId));
    }
}
