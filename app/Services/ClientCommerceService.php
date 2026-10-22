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
        $everPaid = $this->hasEverPaidForPackage($user->client, (int) $package->id);

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
            $label = $everPaid ? 'Perpanjang' : 'Beli '.$package->name;

            return ['action' => 'checkout', 'label' => $label, 'enabled' => true];
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

    public function beginCheckout(User $user, int $packageId, string $applicationSlug, ?string $referralCode): array
    {
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
    public function pendingOrdersForUser(User $user): array
    {
        return PaymentOrder::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['package:id,name', 'application:id,name'])
            ->latest('id')
            ->get()
            ->map(fn (PaymentOrder $order) => [
                'order_id' => $order->order_id,
                'amount_label' => 'Rp '.number_format((float) $order->amount, 0, ',', '.'),
                'status' => $order->status,
                'payment_page_url' => $order->payment_page_url,
                'expires_at_label' => $order->expires_at?->format('d M Y H:i'),
                'package_name' => $order->package?->name,
                'application_name' => $order->application?->name,
                'simulate_available' => $this->paymentService->allowsSimulatePayment(),
            ])
            ->values()
            ->all();
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
