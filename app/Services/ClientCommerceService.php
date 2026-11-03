<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\Client;
use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Support\Carbon;

class ClientCommerceService
{
    public function __construct(
        private LicenseService $licenseService,
        private PaymentService $paymentService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function planCtasForApplication(?User $user, Application $application, array $planRows): array
    {
        $packages = ApplicationPackage::query()
            ->where('application_id', $application->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        return collect($planRows)
            ->map(function (array $row) use ($user, $application, $packages) {
                $packageId = (int) ($row['package_id'] ?? 0);
                $package = $packageId > 0 ? $packages->get($packageId) : null;

                if (! $package) {
                    return array_merge($row, [
                        'cta_action' => 'register',
                        'cta_label' => 'Daftar',
                    ]);
                }

                $cta = $this->resolvePlanCta($user, $application, $package);

                return array_merge($row, [
                    'is_trial' => $this->isTrialLikePackage($package),
                    'cta_action' => $cta['action'],
                    'cta_label' => $cta['label'],
                    'cta_enabled' => $cta['enabled'],
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * @return array{action: string, label: string, enabled: bool}
     */
    public function resolvePlanCta(?User $user, Application $application, ApplicationPackage $package): array
    {
        if ($this->isTrialLikePackage($package)) {
            if (! $user?->client) {
                return ['action' => 'trial', 'label' => 'Coba Gratis', 'enabled' => true];
            }

            if ($this->canActivateTrial($user, $application)) {
                return ['action' => 'trial', 'label' => 'Coba Gratis', 'enabled' => true];
            }

            $owned = $this->ownedApplication($user, $application);

            if ($owned) {
                return ['action' => 'check_license', 'label' => 'Lihat Lisensi', 'enabled' => true];
            }

            return ['action' => 'none', 'label' => '—', 'enabled' => false];
        }

        if (! $user?->client) {
            return ['action' => 'checkout', 'label' => 'Beli '.$package->name, 'enabled' => true];
        }

        $owned = $this->ownedApplication($user, $application);
        $currentPackage = $this->currentPackage($owned);
        $active = $owned && $this->isPivotActive($owned);
        $expired = $owned && ! $active;
        $isCurrent = $currentPackage && (int) $currentPackage->id === (int) $package->id;

        if ($isCurrent && $active) {
            return ['action' => 'check_license', 'label' => 'Lihat Lisensi', 'enabled' => true];
        }

        if ($isCurrent && $expired) {
            return ['action' => 'checkout', 'label' => 'Perpanjang', 'enabled' => true];
        }

        if ($active && $currentPackage) {
            if ($this->packageRank($package) > $this->packageRank($currentPackage)) {
                return ['action' => 'checkout', 'label' => 'Beli '.$package->name, 'enabled' => true];
            }

            return ['action' => 'none', 'label' => '—', 'enabled' => false];
        }

        if ($expired || ! $owned) {
            return ['action' => 'checkout', 'label' => 'Beli '.$package->name, 'enabled' => true];
        }

        return ['action' => 'checkout', 'label' => 'Beli '.$package->name, 'enabled' => true];
    }

    /**
     * Auto-trial setelah login desktop (API), selaras dengan tombol Coba Gratis di web.
     *
     * @return array{activated: bool, message?: string}
     */
    public function attemptAutoTrialOnDesktopLogin(User $user, ?string $applicationSlug = null): array
    {
        $slug = trim((string) ($applicationSlug ?: config('softsale.default_application_slug', 'spot-color')));
        if ($slug === '') {
            $slug = 'spot-color';
        }

        $application = $this->resolveApplication($slug);
        if (! $application || ! $this->canActivateTrial($user, $application)) {
            return ['activated' => false];
        }

        $result = $this->activateTrial($user, $slug);
        if (($result['status'] ?? '') !== 'success') {
            return ['activated' => false];
        }

        return [
            'activated' => true,
            'message' => (string) ($result['message'] ?? 'Trial berhasil diaktifkan.'),
        ];
    }

    public function activateTrial(User $user, string $applicationSlug): array
    {
        $user->loadMissing('client');
        if (! $user->client) {
            return ['status' => 'error', 'message' => 'Profil client tidak ditemukan.'];
        }

        $application = $this->resolveApplication($applicationSlug);
        if (! $application) {
            return ['status' => 'error', 'message' => 'Aplikasi tidak ditemukan.'];
        }

        if (! $this->canActivateTrial($user, $application)) {
            return ['status' => 'error', 'message' => 'Trial tidak tersedia untuk akun ini.'];
        }

        $trialPackage = ApplicationPackage::query()
            ->where('application_id', $application->id)
            ->where('is_trial', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (! $trialPackage) {
            return ['status' => 'error', 'message' => 'Paket trial belum dikonfigurasi.'];
        }

        if ((float) $trialPackage->price > 0) {
            return $this->beginCheckout($user, (int) $trialPackage->id, $applicationSlug, null);
        }

        $this->licenseService->applyPackageForClient(
            $user,
            $application,
            $trialPackage,
            null,
            'trial',
        );

        return [
            'status' => 'success',
            'message' => 'Trial berhasil diaktifkan.',
            'license' => $this->licenseService->listForUser($user),
        ];
    }

    public function beginCheckout(
        User $user,
        int $packageId,
        string $applicationSlug,
        ?string $referralCode,
        ?int $totalDevices = null,
    ): array {
        $validation = $this->validateCheckout($user, $packageId, $applicationSlug);
        if (($validation['status'] ?? '') === 'error') {
            return $validation;
        }

        /** @var Application $application */
        $application = $validation['application'];
        /** @var ApplicationPackage $targetPackage */
        $targetPackage = $validation['target_package'];
        $orderType = (string) $validation['order_type'];

        return $this->paymentService->createPackageOrder(
            $user,
            $targetPackage,
            $application,
            $orderType,
            $validation['current_package'] ?? null,
            $referralCode,
            $totalDevices,
        );
    }

    public function beginDeviceSlotCheckout(
        User $user,
        string $applicationSlug,
        int $additionalSlots,
        ?string $referralCode,
    ): array {
        $user->loadMissing('client');
        if (! $user->client) {
            return ['status' => 'error', 'message' => 'Akun client tidak ditemukan.'];
        }

        $application = $this->resolveApplication($applicationSlug);
        if (! $application) {
            return ['status' => 'error', 'message' => 'Aplikasi tidak ditemukan.'];
        }

        $owned = $this->ownedApplication($user, $application);
        if (! $owned || ! $this->isPivotActive($owned)) {
            return ['status' => 'error', 'message' => 'Lisensi tidak aktif.'];
        }

        $currentPackage = $this->currentPackage($owned);
        if (! $currentPackage || $this->isTrialLikePackage($currentPackage)) {
            return ['status' => 'error', 'message' => 'Tambah perangkat tidak tersedia untuk paket ini.'];
        }

        return $this->paymentService->createDeviceSlotOrder(
            $user,
            $application,
            $currentPackage,
            $additionalSlots,
            $referralCode,
        );
    }

    public function shouldRedirectToLicenseAfterAuth(Client $client, Application $application, ApplicationPackage $targetPackage): bool
    {
        if ($targetPackage->is_trial) {
            return false;
        }

        return $this->hasEverPaidForPackage($client, (int) $targetPackage->id);
    }

    /**
     * @return array<string, mixed>
     */
    public function validateCheckout(User $user, int $packageId, string $applicationSlug): array
    {
        if ($packageId <= 0) {
            return ['status' => 'error', 'message' => 'Paket wajib dipilih.'];
        }

        $user->loadMissing('client');
        if (! $user->client) {
            return ['status' => 'error', 'message' => 'Akun client tidak ditemukan.'];
        }

        $application = $this->resolveApplication($applicationSlug);
        if (! $application) {
            return ['status' => 'error', 'message' => 'Aplikasi tidak ditemukan.'];
        }

        $targetPackage = ApplicationPackage::query()
            ->where('id', $packageId)
            ->where('application_id', $application->id)
            ->first();

        if (! $targetPackage) {
            return ['status' => 'error', 'message' => 'Paket tidak valid.'];
        }

        if ($targetPackage->is_trial) {
            return ['status' => 'error', 'message' => 'Gunakan tombol Coba Gratis untuk trial.'];
        }

        $owned = $this->ownedApplication($user, $application);
        $currentPackage = $this->currentPackage($owned);
        $active = $owned && $this->isPivotActive($owned);

        if ($active && $currentPackage && (int) $currentPackage->id === (int) $targetPackage->id) {
            return ['status' => 'error', 'message' => 'Paket ini masih aktif. Buka halaman lisensi.'];
        }

        if ($active && $currentPackage && $this->packageRank($targetPackage) <= $this->packageRank($currentPackage)) {
            return ['status' => 'error', 'message' => 'Downgrade hanya bisa setelah masa aktif berakhir.'];
        }

        $orderType = 'purchase';
        if ($owned) {
            if ($active) {
                $orderType = 'upgrade';
            } elseif ($currentPackage && $this->isTrialLikePackage($currentPackage)) {
                $orderType = 'upgrade';
            } elseif ($currentPackage && (int) $currentPackage->id === (int) $targetPackage->id) {
                $orderType = 'renew';
            } elseif ($this->hasEverPaidForPackage($user->client, (int) $targetPackage->id)) {
                $orderType = 'renew';
            } else {
                $orderType = 'purchase';
            }
        }

        return [
            'status' => 'success',
            'application' => $application,
            'target_package' => $targetPackage,
            'current_package' => $currentPackage,
            'order_type' => $orderType,
        ];
    }

    public function canActivateTrial(User $user, Application $application): bool
    {
        $user->loadMissing('client');
        if (! $user->client) {
            return false;
        }

        $owned = $this->ownedApplication($user, $application);
        if ($owned) {
            return false;
        }

        return true;
    }

    public function hasEverPaidForPackage(Client $client, int $packageId): bool
    {
        return PaymentOrder::query()
            ->where('client_id', $client->id)
            ->where('application_package_id', $packageId)
            ->where('status', 'paid')
            ->exists();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pendingOrdersForUser(User $user, bool $reconcileStale = true): array
    {
        if ($reconcileStale) {
            $this->paymentService->reconcileStalePendingOrdersForUser($user);
            $this->paymentService->expireLocalOverduePendingOrdersForUser($user);
        }

        return PaymentOrder::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'expired'])
            ->with(['package:id,name', 'application:id,name,slug'])
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(function (PaymentOrder $order) {
                $pastDue = $order->expires_at instanceof Carbon
                    && $order->expires_at->isPast();
                $payable = $order->status === 'pending'
                    && ! $pastDue
                    && ($order->expires_at === null || $order->expires_at->isFuture());

                return [
                    'order_id' => $order->order_id,
                    'type' => $order->type,
                    'description' => $this->billingOrderDescription($order),
                    'amount_label' => 'Rp '.number_format((float) $order->amount, 0, ',', '.'),
                    'status' => $order->status,
                    'status_label' => ($payable && $order->status === 'pending')
                        ? 'Menunggu bayar'
                        : 'Expired',
                    'is_payable' => $payable,
                    'payment_page_url' => $order->payment_page_url,
                    'created_at' => $order->created_at?->toIso8601String(),
                    'created_at_label' => $order->created_at?->format('d M Y H:i'),
                    'expires_at' => $order->expires_at?->toIso8601String(),
                    'expires_at_label' => $order->expires_at?->format('d M Y H:i'),
                    'package_name' => $order->package?->name,
                    'application_name' => $order->application?->name,
                    'application_slug' => $order->application?->slug,
                    'simulate_available' => $payable && $this->paymentService->allowsSimulatePayment(),
                ];
            })
            ->values()
            ->all();
    }

    public function billingOrderDescription(PaymentOrder $order): string
    {
        $order->loadMissing(['application:id,name', 'package:id,name']);

        $applicationName = $order->application?->name ?? 'Aplikasi';
        $packageName = $order->package?->name ?? 'Paket';

        if ($order->type === 'device_slots') {
            $additional = (int) data_get($order->meta, 'additional_device_slots', 0);

            return $this->formatDeviceSlotsBillingDescription($applicationName, $packageName, $additional);
        }

        if ($order->type === 'upgrade') {
            $fromPackageId = (int) data_get($order->meta, 'current_package_id', 0);
            $extraSlots = $this->extraDeviceSlotsFromMeta($order->meta);

            return $this->formatUpgradeBillingDescription(
                $applicationName,
                $packageName,
                $fromPackageId > 0 ? $this->packageNameById($fromPackageId) : null,
                $extraSlots,
            );
        }

        $extraSlots = $this->extraDeviceSlotsFromMeta($order->meta);

        return match ($order->type) {
            'renew' => $this->formatPackageBillingDescription('Perpanjang', $applicationName, $packageName, $extraSlots),
            'purchase' => $this->formatPackageBillingDescription('Beli', $applicationName, $packageName, $extraSlots),
            default => $this->formatPackageBillingDescription('Pembayaran', $applicationName, $packageName, $extraSlots),
        };
    }

    public function billingTransactionDescription(\App\Models\Transaction $transaction): string
    {
        $transaction->loadMissing(['application:id,name', 'package:id,name']);

        $applicationName = $transaction->application?->name ?? 'Aplikasi';
        $packageName = $transaction->package?->name ?? 'Paket';

        if ($transaction->type === 'device_slots') {
            $additional = (int) data_get($transaction->meta, 'additional_device_slots', 0);
            if ($additional <= 0 && is_string($transaction->description) && preg_match('/\+(\d+)/', $transaction->description, $m)) {
                $additional = (int) $m[1];
            }

            return $this->formatDeviceSlotsBillingDescription($applicationName, $packageName, $additional);
        }

        if ($transaction->type === 'upgrade') {
            $fromPackageId = (int) data_get($transaction->meta, 'from_package_id', 0);
            $fromName = $fromPackageId > 0
                ? $this->packageNameById($fromPackageId)
                : $this->parseUpgradeFromNameFromLegacyDescription($transaction->description);
            $extraSlots = $this->extraDeviceSlotsFromMeta($transaction->meta);

            return $this->formatUpgradeBillingDescription($applicationName, $packageName, $fromName, $extraSlots);
        }

        $extraSlots = $this->extraDeviceSlotsFromMeta($transaction->meta);

        return match ($transaction->type) {
            'renew' => $this->formatPackageBillingDescription('Perpanjang', $applicationName, $packageName, $extraSlots),
            'purchase' => $this->formatPackageBillingDescription('Beli', $applicationName, $packageName, $extraSlots),
            default => $this->formatPackageBillingDescription(
                $this->humanizeBillingAction($transaction->type),
                $applicationName,
                $packageName,
                $extraSlots,
            ),
        };
    }

    private function extraDeviceSlotsFromMeta(mixed $meta): int
    {
        return max(0, (int) data_get($meta, 'extra_device_slots', 0));
    }

    private function formatPackageBillingDescription(
        string $action,
        string $applicationName,
        string $packageName,
        int $extraDeviceSlots = 0,
    ): string {
        $packageLabel = $this->formatPackageLabelWithExtraDevices($packageName, $extraDeviceSlots);

        return sprintf('%s — %s (%s)', $action, $applicationName, $packageLabel);
    }

    private function formatPackageLabelWithExtraDevices(string $packageName, int $extraDeviceSlots): string
    {
        $name = trim($packageName) !== '' ? trim($packageName) : 'Paket';
        if ($extraDeviceSlots <= 0) {
            return $name;
        }

        return sprintf('%s · +%d device', $name, $extraDeviceSlots);
    }

    private function formatDeviceSlotsBillingDescription(
        string $applicationName,
        string $packageName,
        int $additionalSlots,
    ): string {
        return sprintf(
            'Tambah device +%d — %s (%s)',
            max(1, $additionalSlots),
            $applicationName,
            $packageName,
        );
    }

    private function formatUpgradeBillingDescription(
        string $applicationName,
        string $targetPackageName,
        ?string $fromPackageName,
        int $extraDeviceSlots = 0,
    ): string {
        $from = trim((string) $fromPackageName);
        $to = trim($targetPackageName);

        if ($from !== '' && $to !== '' && strcasecmp($from, $to) !== 0) {
            $detail = sprintf('%s → %s', $from, $this->formatPackageLabelWithExtraDevices($to, $extraDeviceSlots));

            return sprintf('Upgrade — %s (%s)', $applicationName, $detail);
        }

        return $this->formatPackageBillingDescription(
            'Upgrade',
            $applicationName,
            $to !== '' ? $to : 'Paket',
            $extraDeviceSlots,
        );
    }

    private function parseUpgradeFromNameFromLegacyDescription(?string $description): ?string
    {
        if (! is_string($description) || $description === '') {
            return null;
        }

        if (preg_match('/:\s*(.+?)\s*→\s*.+$/u', $description, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/Upgrade\s+.+?\s*—\s*(.+)$/u', $description, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function packageNameById(int $packageId): ?string
    {
        if ($packageId <= 0) {
            return null;
        }

        return ApplicationPackage::query()->whereKey($packageId)->value('name');
    }

    private function humanizeBillingAction(?string $type): string
    {
        return match ($type) {
            'renew' => 'Perpanjang',
            'purchase' => 'Beli',
            'upgrade' => 'Upgrade',
            'device_slots' => 'Tambah device',
            default => ucfirst((string) $type),
        };
    }

    private function hasAnyPaidOrderForApplication(Client $client, Application $application): bool
    {
        return PaymentOrder::query()
            ->where('client_id', $client->id)
            ->where('application_id', $application->id)
            ->where('status', 'paid')
            ->exists();
    }

    private function isTrialLikePackage(ApplicationPackage $package): bool
    {
        if ($package->is_trial) {
            return true;
        }

        if ((float) $package->price > 0) {
            return false;
        }

        $name = strtolower(trim($package->name));

        return $name === 'trial' || str_contains($name, 'trial');
    }

    private function ownedApplication(User $user, Application $application): ?Application
    {
        return $user->client?->applications()
            ->where('applications.id', $application->id)
            ->first();
    }

    private function currentPackage(?Application $owned): ?ApplicationPackage
    {
        if (! $owned || ! $owned->pivot->application_package_id) {
            return null;
        }

        return ApplicationPackage::find($owned->pivot->application_package_id);
    }

    private function isPivotActive(Application $owned): bool
    {
        $expiresAt = $owned->pivot->expires_at
            ? Carbon::parse($owned->pivot->expires_at)
            : null;

        if ($expiresAt && now()->greaterThan($expiresAt)) {
            return false;
        }

        return in_array((string) $owned->pivot->status, ['active', 'trial'], true);
    }

    private function packageRank(ApplicationPackage $package): int
    {
        return ((int) $package->sort_order * 100000) + (int) round((float) $package->price);
    }

    private function resolveApplication(string $slug): ?Application
    {
        return Application::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
