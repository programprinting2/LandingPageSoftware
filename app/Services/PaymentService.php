<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\PaymentOrder;
use App\Models\Transaction;
use App\Models\User;
use App\Support\AffiliateReferralCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private KomercePaymentService $komerce,
        private LicenseService $licenseService,
        private TransactionService $transactionService,
        private CheckoutReferralService $checkoutReferral,
    ) {}

    public function createPackageOrder(
        User $user,
        ApplicationPackage $targetPackage,
        Application $application,
        string $orderType,
        ?ApplicationPackage $currentPackage,
        ?string $referralCode = null,
        ?int $totalDevicesTarget = null,
    ): array {
        $referral = $this->checkoutReferral->resolve($referralCode, $application);

        if ($referralCode !== null && $referralCode !== '' && ! $referral) {
            return [
                'status' => 'error',
                'message' => 'Kode voucher tidak valid atau tidak aktif.',
            ];
        }

        $affiliate = $referral['affiliate'] ?? null;
        $promoVoucher = $referral['promo_voucher'] ?? null;

        $deviceSlots = app(DeviceSlotService::class);
        $baseQuota = max(1, (int) $targetPackage->devices_quota);
        $totalDevices = $totalDevicesTarget ?? $baseQuota;
        $slotCheck = $deviceSlots->validateTotalDevicesForCheckout($targetPackage, $totalDevices);

        if (($slotCheck['status'] ?? '') === 'error') {
            return $slotCheck;
        }

        $extraSlots = (int) ($slotCheck['extra_slots'] ?? 0);
        $slotLineAmount = $deviceSlots->slotLineAmount($targetPackage, $extraSlots);

        if ($extraSlots > 0 && $slotLineAmount <= 0) {
            return [
                'status' => 'error',
                'message' => 'Harga slot perangkat belum dikonfigurasi untuk paket ini.',
            ];
        }

        $listPrice = (float) $targetPackage->price + $slotLineAmount;
        $discountPercent = $this->checkoutDiscountPercent($targetPackage, $referral);
        $amount = (int) round($listPrice * (1 - ($discountPercent / 100)));
        $minAmount = (int) config('komerce.min_amount', 10000);

        if ($amount < $minAmount) {
            return [
                'status' => 'error',
                'message' => 'Nominal paket di bawah minimum QRIS (Rp '.number_format($minAmount, 0, ',', '.').').',
            ];
        }

        if (! $this->komerce->isConfigured()) {
            return [
                'status' => 'error',
                'message' => 'Pembayaran QRIS belum dikonfigurasi. Hubungi admin.',
            ];
        }

        $user->loadMissing('client');
        $orderId = $this->generateOrderId();

        $slotSuffix = $extraSlots > 0 ? sprintf(' + %d slot perangkat', $extraSlots) : '';
        $itemLabel = match ($orderType) {
            'renew' => sprintf('Perpanjang %s — %s%s', $application->name, $targetPackage->name, $slotSuffix),
            'purchase' => sprintf('Beli %s — %s%s', $application->name, $targetPackage->name, $slotSuffix),
            default => sprintf('Upgrade %s — %s%s', $application->name, $targetPackage->name, $slotSuffix),
        };

        $payload = [
            'order_id' => $orderId,
            'payment_type' => 'qris',
            'amount' => $amount,
            'customer' => [
                'name' => $user->name ?: ($user->client?->company_name ?? 'Client'),
                'email' => $user->email,
                'phone' => $user->client?->whatsapp ?: '080000000000',
            ],
            'items' => [[
                'name' => $itemLabel,
                'quantity' => 1,
                'price' => $amount,
            ]],
            'callback_url' => $this->komerce->callbackUrl(),
            'callback_API_KEY' => (string) config('komerce.callback_api_key'),
        ];

        $created = $this->komerce->createQrisPayment($payload);

        if (! ($created['ok'] ?? false)) {
            return [
                'status' => 'error',
                'message' => $created['message'] ?? 'Gagal membuat pembayaran.',
            ];
        }

        $data = is_array($created['data'] ?? null) ? $created['data'] : [];
        $paymentId = (string) ($data['payment_id'] ?? $data['id'] ?? '');
        $qrString = (string) ($data['qr_string'] ?? $data['qr_code'] ?? '');
        $paymentToken = (string) ($data['token'] ?? $data['payment_token'] ?? '');
        $paymentPageUrl = $paymentToken
            ? rtrim((string) config('komerce.payment_page_url'), '/').'/'.$paymentToken
            : (string) ($data['payment_url'] ?? $data['checkout_url'] ?? '');

        $expiresAt = $this->parseDate($data['expired_at'] ?? $data['expires_at'] ?? null) ?? now()->addMinutes(5);

        $order = PaymentOrder::create([
            'order_id' => $orderId,
            'user_id' => $user->id,
            'client_id' => $user->client->id,
            'application_id' => $application->id,
            'application_package_id' => $targetPackage->id,
            'affiliate_id' => $affiliate?->id,
            'promo_voucher_id' => $promoVoucher?->id,
            'referral_code' => $referral['code'] ?? null,
            'type' => in_array($orderType, ['purchase', 'renew', 'upgrade'], true) ? $orderType : 'upgrade',
            'amount' => $amount,
            'payment_type' => 'qris',
            'komerce_payment_id' => $paymentId ?: null,
            'status' => 'pending',
            'qr_string' => $qrString ?: null,
            'payment_page_url' => $paymentPageUrl ?: null,
            'expires_at' => $expiresAt,
            'meta' => [
                'current_package_id' => $currentPackage?->id,
                'list_price' => $listPrice,
                'package_list_price' => (float) $targetPackage->price,
                'slot_line_amount' => $slotLineAmount,
                'extra_device_slots' => $extraSlots,
                'total_devices_target' => (int) ($slotCheck['total_devices'] ?? $totalDevices),
                'discount_percent' => $discountPercent,
                'discount_amount' => round($listPrice - $amount, 2),
                'komerce_create' => $data,
            ],
        ]);

        return [
            'status' => 'success',
            'message' => 'Pembayaran QRIS dibuat. Selesaikan pembayaran sebelum kedaluwarsa.',
            'payment_required' => true,
            'sandbox_simulate' => $this->allowsSimulatePayment(),
            'referral_code' => $referral['code'] ?? null,
            'payment' => $this->formatPaymentOrder($order),
        ];
    }

    public function createUpgradePayment(User $user, int $packageId, string $applicationSlug = 'spot-color', ?string $referralCode = null): array
    {
        return app(ClientCommerceService::class)->beginCheckout($user, $packageId, $applicationSlug, $referralCode);
    }

    public function createDeviceSlotOrder(
        User $user,
        Application $application,
        ApplicationPackage $activePackage,
        int $additionalSlots,
        ?string $referralCode = null,
    ): array {
        $deviceSlots = app(DeviceSlotService::class);
        $validation = $deviceSlots->validateAdditionalSlotsPurchase($user, $application, $activePackage, $additionalSlots);

        if (($validation['status'] ?? '') === 'error') {
            return $validation;
        }

        $additionalSlots = (int) ($validation['additional_slots'] ?? 0);
        $referral = $this->checkoutReferral->resolve($referralCode, $application);

        if ($referralCode !== null && $referralCode !== '' && ! $referral) {
            return [
                'status' => 'error',
                'message' => 'Kode voucher tidak valid atau tidak aktif.',
            ];
        }

        $affiliate = $referral['affiliate'] ?? null;
        $promoVoucher = $referral['promo_voucher'] ?? null;

        $listPrice = $deviceSlots->slotLineAmount($activePackage, $additionalSlots);
        $discountPercent = $this->checkoutDiscountPercent($activePackage, $referral);
        $amount = (int) round($listPrice * (1 - ($discountPercent / 100)));
        $minAmount = (int) config('komerce.min_amount', 10000);

        if ($amount < $minAmount) {
            return [
                'status' => 'error',
                'message' => 'Nominal di bawah minimum QRIS (Rp '.number_format($minAmount, 0, ',', '.').').',
            ];
        }

        if (! $this->komerce->isConfigured()) {
            return [
                'status' => 'error',
                'message' => 'Pembayaran QRIS belum dikonfigurasi. Hubungi admin.',
            ];
        }

        $user->loadMissing('client');
        $orderId = $this->generateOrderId();
        $itemLabel = sprintf(
            'Slot perangkat +%d — %s (%s)',
            $additionalSlots,
            $application->name,
            $activePackage->name,
        );

        $payload = [
            'order_id' => $orderId,
            'payment_type' => 'qris',
            'amount' => $amount,
            'customer' => [
                'name' => $user->name ?: ($user->client?->company_name ?? 'Client'),
                'email' => $user->email,
                'phone' => $user->client?->whatsapp ?: '080000000000',
            ],
            'items' => [[
                'name' => $itemLabel,
                'quantity' => 1,
                'price' => $amount,
            ]],
            'callback_url' => $this->komerce->callbackUrl(),
            'callback_API_KEY' => (string) config('komerce.callback_api_key'),
        ];

        $created = $this->komerce->createQrisPayment($payload);

        if (! ($created['ok'] ?? false)) {
            return [
                'status' => 'error',
                'message' => $created['message'] ?? 'Gagal membuat pembayaran.',
            ];
        }

        $data = is_array($created['data'] ?? null) ? $created['data'] : [];
        $paymentId = (string) ($data['payment_id'] ?? $data['id'] ?? '');
        $qrString = (string) ($data['qr_string'] ?? $data['qr_code'] ?? '');
        $paymentToken = (string) ($data['token'] ?? $data['payment_token'] ?? '');
        $paymentPageUrl = $paymentToken
            ? rtrim((string) config('komerce.payment_page_url'), '/').'/'.$paymentToken
            : (string) ($data['payment_url'] ?? $data['checkout_url'] ?? '');

        $expiresAt = $this->parseDate($data['expired_at'] ?? $data['expires_at'] ?? null) ?? now()->addMinutes(5);

        $order = PaymentOrder::create([
            'order_id' => $orderId,
            'user_id' => $user->id,
            'client_id' => $user->client->id,
            'application_id' => $application->id,
            'application_package_id' => $activePackage->id,
            'affiliate_id' => $affiliate?->id,
            'promo_voucher_id' => $promoVoucher?->id,
            'referral_code' => $referral['code'] ?? null,
            'type' => 'device_slots',
            'amount' => $amount,
            'payment_type' => 'qris',
            'komerce_payment_id' => $paymentId ?: null,
            'status' => 'pending',
            'qr_string' => $qrString ?: null,
            'payment_page_url' => $paymentPageUrl ?: null,
            'expires_at' => $expiresAt,
            'meta' => [
                'additional_device_slots' => $additionalSlots,
                'list_price' => $listPrice,
                'discount_percent' => $discountPercent,
                'discount_amount' => round($listPrice - $amount, 2),
                'komerce_create' => $data,
            ],
        ]);

        return [
            'status' => 'success',
            'message' => 'Pembayaran QRIS dibuat. Selesaikan pembayaran sebelum kedaluwarsa.',
            'payment_required' => true,
            'sandbox_simulate' => $this->allowsSimulatePayment(),
            'referral_code' => $referral['code'] ?? null,
            'payment' => $this->formatPaymentOrder($order),
        ];
    }

    /**
     * @param  array{type: string, code: string, affiliate: ?Affiliate, promo_voucher: ?\App\Models\PromoVoucher, discount_percent: float}|null  $referral
     */
    private function checkoutDiscountPercent(ApplicationPackage $package, ?array $referral): float
    {
        if (! $referral) {
            return 0.0;
        }

        if ($referral['type'] === 'promo') {
            return max(0, min(100, (float) ($referral['discount_percent'] ?? 0)));
        }

        return max(0, min(100, (float) $package->discount_percent));
    }

    public function simulatePayment(User $user, string $orderId): array
    {
        if (! $this->allowsSimulatePayment()) {
            return [
                'status' => 'error',
                'message' => 'Simulasi bayar hanya tersedia di mode sandbox.',
            ];
        }

        $order = PaymentOrder::query()
            ->where('order_id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (! $order) {
            return [
                'status' => 'error',
                'message' => 'Pesanan pembayaran tidak ditemukan.',
            ];
        }

        if ($order->status === 'paid') {
            return $this->getOrderStatus($user, $orderId);
        }

        if ($order->status !== 'pending') {
            return [
                'status' => 'error',
                'message' => 'Pesanan tidak dapat disimulasikan (status: '.$order->status.').',
            ];
        }

        if ($order->expires_at instanceof Carbon && $order->expires_at->isPast()) {
            $this->syncOrderFromGateway($order);

            return [
                'status' => 'error',
                'message' => 'QRIS sudah kedaluwarsa di payment gateway. Buat pembayaran baru.',
            ];
        }

        $externalId = (string) data_get($order->meta, 'komerce_create.external_id', '');

        if ($externalId === '') {
            return [
                'status' => 'error',
                'message' => 'Data external_id gateway tidak ditemukan untuk pesanan ini.',
            ];
        }

        if (! $order->komerce_payment_id) {
            return [
                'status' => 'error',
                'message' => 'Payment ID gateway tidak ditemukan.',
            ];
        }

        $simulated = $this->komerce->simulateSandboxPayment(
            $this->komerce->sandboxMethodSlug((string) $order->payment_type),
            $externalId,
            (int) round((float) $order->amount),
        );

        if (! ($simulated['ok'] ?? false)) {
            $this->syncOrderFromGateway($order);

            return [
                'status' => 'error',
                'message' => $simulated['message'] ?? 'Gagal mensimulasikan pembayaran di gateway Komerce.',
            ];
        }

        $fulfillSource = [
            'gateway_simulated' => true,
            'simulate_response' => $simulated['response'] ?? [],
        ];

        $paid = $this->pollGatewayPaidAndFulfill($order, $fulfillSource, 8, 500000);

        if (! $paid) {
            $order->refresh();

            if ($order->status === 'paid') {
                $paid = true;
            }
        }

        if (! $paid) {
            return [
                'status' => 'error',
                'message' => 'Simulasi gateway diproses, tetapi pembayaran belum terkonfirmasi. Tunggu beberapa detik lalu gunakan Cek Status Pembayaran.',
            ];
        }

        return array_merge(
            [
                'status' => 'success',
                'message' => 'Simulasi pembayaran gateway berhasil. Lisensi diperbarui.',
            ],
            $this->getOrderStatus($user, $orderId),
        );
    }

    private function pollGatewayPaidAndFulfill(PaymentOrder $order, array $fulfillSource, int $attempts, int $sleepMicros): bool
    {
        if (! $order->komerce_payment_id) {
            return false;
        }

        for ($i = 0; $i < $attempts; $i++) {
            if ($i > 0) {
                usleep($sleepMicros);
            }

            $remote = $this->komerce->getPaymentStatus($order->komerce_payment_id);

            if (! ($remote['ok'] ?? false) || ! is_array($remote['data'] ?? null)) {
                continue;
            }

            $remoteStatus = $this->komerce->normalizePaymentStatus(
                (string) ($remote['data']['status'] ?? $remote['data']['payment_status'] ?? '')
            );

            if ($remoteStatus === 'paid') {
                $this->fulfillOrder($order, array_merge($remote['data'], $fulfillSource));

                return true;
            }

            if (in_array($remoteStatus, ['expired', 'canceled'], true)) {
                $order->update([
                    'status' => $remoteStatus,
                    'meta' => array_merge($order->meta ?? [], [
                        'komerce_status_sync' => $remote['data'],
                    ]),
                ]);

                return false;
            }
        }

        return false;
    }

    public function reconcileStalePendingOrdersForUser(User $user): void
    {
        $pending = PaymentOrder::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pending as $order) {
            if ($order->expires_at instanceof Carbon && $order->expires_at->isPast()) {
                $this->syncOrderFromGateway($order);
            }
        }
    }

    public function syncOrderFromGateway(PaymentOrder $order): PaymentOrder
    {
        if (! $order->komerce_payment_id || ! $this->komerce->isConfigured()) {
            $this->expireOrderIfPastDue($order);

            return $order->refresh();
        }

        $remote = $this->komerce->getPaymentStatus($order->komerce_payment_id);

        if (! ($remote['ok'] ?? false) || ! is_array($remote['data'] ?? null)) {
            $this->expireOrderIfPastDue($order);

            return $order->refresh();
        }

        $remoteStatus = $this->komerce->normalizePaymentStatus(
            (string) ($remote['data']['status'] ?? $remote['data']['payment_status'] ?? '')
        );

        if ($remoteStatus === 'paid' && $order->status !== 'paid') {
            $this->fulfillOrder($order, $remote['data']);
        } elseif (in_array($remoteStatus, ['expired', 'canceled'], true) && $order->status === 'pending') {
            $order->update([
                'status' => $remoteStatus,
                'meta' => array_merge($order->meta ?? [], [
                    'komerce_status_sync' => $remote['data'],
                ]),
            ]);
        } elseif ($remoteStatus === 'pending' && $order->status === 'paid') {
            // Local marked paid without gateway confirmation — keep gateway as source of truth in meta only.
            $order->update([
                'meta' => array_merge($order->meta ?? [], [
                    'gateway_mismatch' => [
                        'local_status' => $order->status,
                        'remote_status' => strtoupper((string) ($remote['data']['status'] ?? '')),
                        'synced_at' => now()->toIso8601String(),
                    ],
                    'komerce_status_sync' => $remote['data'],
                ]),
            ]);
        } else {
            $order->update([
                'meta' => array_merge($order->meta ?? [], [
                    'komerce_status_sync' => $remote['data'],
                ]),
            ]);
        }

        $order->refresh();
        $this->expireOrderIfPastDue($order);

        return $order->refresh();
    }

    public function expireLocalOverduePendingOrdersForUser(User $user): void
    {
        PaymentOrder::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    private function expireOrderIfPastDue(PaymentOrder $order): void
    {
        if ($order->status !== 'pending') {
            return;
        }

        if ($order->expires_at instanceof Carbon && $order->expires_at->isPast()) {
            $order->update(['status' => 'expired']);
        }
    }

    public function getOrderStatus(User $user, string $orderId): array
    {
        $order = PaymentOrder::query()
            ->where('order_id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (! $order) {
            return [
                'status' => 'error',
                'message' => 'Pesanan pembayaran tidak ditemukan.',
            ];
        }

        if ($order->status === 'pending' && $order->komerce_payment_id && $this->komerce->isConfigured()) {
            $remote = $this->komerce->getPaymentStatus($order->komerce_payment_id);

            if (($remote['ok'] ?? false) && is_array($remote['data'] ?? null)) {
                $remoteStatus = $this->komerce->normalizePaymentStatus(
                    (string) ($remote['data']['payment_status'] ?? $remote['data']['status'] ?? '')
                );

                if ($remoteStatus === 'paid') {
                    $this->fulfillOrder($order, $remote['data']);
                    $order->refresh();
                } elseif (in_array($remoteStatus, ['expired', 'canceled'], true)) {
                    $order->update(['status' => $remoteStatus]);
                    $order->refresh();
                }
            }
        }

        $payload = [
            'status' => 'success',
            'payment' => $this->formatPaymentOrder($order),
        ];

        if ($order->status === 'paid') {
            $user->load('client.applications');
            $application = Application::find($order->application_id);
            if ($application) {
                $payload = array_merge(
                    $payload,
                    $this->licenseService->licenseInfoPayload($user, $application),
                    [
                        'features' => $this->licenseService->featuresForUser($user, $application),
                    ],
                    $this->licenseService->serverTimePayload(),
                );
            }
        }

        return $payload;
    }

    public function handleWebhook(string $rawBody, ?string $signature): array
    {
        if (! $this->komerce->verifyCallbackSignature($rawBody, $signature)) {
            return [
                'http_status' => 401,
                'body' => [
                    'success' => false,
                    'message' => 'Invalid callback signature',
                ],
            ];
        }

        $payload = json_decode($rawBody, true);

        if (! is_array($payload)) {
            return [
                'http_status' => 400,
                'body' => [
                    'success' => false,
                    'message' => 'Invalid JSON payload',
                ],
            ];
        }

        $orderId = (string) (
            data_get($payload, 'order_id')
            ?? data_get($payload, 'data.order_id')
            ?? data_get($payload, 'data.reference_id')
            ?? ''
        );

        $paymentStatus = $this->komerce->normalizePaymentStatus(
            (string) (
                data_get($payload, 'payment_status')
                ?? data_get($payload, 'status')
                ?? data_get($payload, 'data.payment_status')
                ?? data_get($payload, 'data.status')
                ?? data_get($payload, 'event')
            )
        );

        if ($orderId === '') {
            return [
                'http_status' => 200,
                'body' => [
                    'success' => true,
                    'message' => 'Webhook received and processed',
                ],
            ];
        }

        $order = PaymentOrder::query()->where('order_id', $orderId)->first();

        if (! $order) {
            return [
                'http_status' => 200,
                'body' => [
                    'success' => true,
                    'message' => 'Webhook received and processed',
                ],
            ];
        }

        if ($paymentStatus === 'paid' && $order->status === 'pending') {
            $this->fulfillOrder($order, $payload);
        } elseif (in_array($paymentStatus, ['expired', 'canceled'], true) && $order->status === 'pending') {
            $order->update([
                'status' => $paymentStatus,
                'meta' => array_merge($order->meta ?? [], ['komerce_webhook' => $payload]),
            ]);
        }

        return [
            'http_status' => 200,
            'body' => [
                'success' => true,
                'message' => 'Webhook received and processed',
            ],
        ];
    }

    public function fulfillOrder(PaymentOrder $order, array $source = []): void
    {
        if ($order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order, $source) {
            $locked = PaymentOrder::query()->lockForUpdate()->find($order->id);

            if (! $locked || $locked->status === 'paid') {
                return;
            }

            $user = User::find($locked->user_id);

            if (! $user) {
                return;
            }

            $application = Application::find($locked->application_id);
            $targetPackage = ApplicationPackage::find($locked->application_package_id);

            if (! $application || ! $targetPackage) {
                return;
            }

            $currentPackageId = (int) data_get($locked->meta, 'current_package_id', 0);
            $currentPackage = $currentPackageId > 0 ? ApplicationPackage::find($currentPackageId) : null;

            $orderType = (string) $locked->type;
            $transaction = null;

            if ($orderType === 'device_slots') {
                $additional = (int) data_get($locked->meta, 'additional_device_slots', 0);
                $this->licenseService->addExtraDeviceSlots($user, $application, $additional);

                $transaction = $this->transactionService->record([
                    'client_id' => $locked->client_id,
                    'user_id' => $user->id,
                    'application_id' => $application->id,
                    'application_package_id' => $targetPackage->id,
                    'type' => 'device_slots',
                    'description' => sprintf(
                        'Tambah device +%d — %s (%s)',
                        max(1, $additional),
                        $application->name,
                        $targetPackage->name,
                    ),
                    'amount' => (float) $locked->amount,
                    'payment_method' => 'qris',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'meta' => [
                        'additional_device_slots' => $additional,
                    ],
                ]);
            } else {
                $packageOrderType = in_array($orderType, ['purchase', 'renew', 'upgrade'], true)
                    ? $orderType
                    : 'upgrade';
                $extraSlots = (int) data_get($locked->meta, 'extra_device_slots', 0);

                $this->licenseService->applyPackageForClient(
                    $user,
                    $application,
                    $targetPackage,
                    $currentPackage,
                    $packageOrderType,
                    $extraSlots,
                );

                $transaction = Transaction::query()
                    ->where('client_id', $locked->client_id)
                    ->where('application_package_id', $targetPackage->id)
                    ->latest('id')
                    ->first();

                if ($transaction) {
                    $transaction->update([
                        'amount' => (float) $locked->amount,
                        'meta' => array_merge($transaction->meta ?? [], [
                            'extra_device_slots' => $extraSlots,
                            'total_devices_target' => data_get($locked->meta, 'total_devices_target'),
                            'slot_line_amount' => data_get($locked->meta, 'slot_line_amount'),
                        ]),
                    ]);
                }
            }

            if ($transaction) {
                $referralMeta = [];

                if ($locked->affiliate_id) {
                    $affiliate = Affiliate::query()->lockForUpdate()->find($locked->affiliate_id);

                    if ($affiliate) {
                        $commissionAmount = $targetPackage->calculateAffiliateCommission((float) $locked->amount);

                        $affiliate->increment('sales_count');

                        if ($commissionAmount > 0) {
                            $affiliate->increment('pending_commission', $commissionAmount);
                        }

                        $referralMeta = [
                            'referral_code' => $locked->referral_code,
                            'affiliate_id' => $affiliate->id,
                            'affiliate_code' => $affiliate->code,
                            'commission_amount' => $commissionAmount,
                            'commission_percent' => (float) $targetPackage->affiliate_commission_percent,
                            'commission_fixed' => (float) $targetPackage->affiliate_commission_fixed,
                        ];
                    }
                }

                $transaction->update([
                    'meta' => array_merge($transaction->meta ?? [], $referralMeta, [
                        'payment_order_id' => $locked->order_id,
                        'gateway' => $this->snapshotGatewayMeta($locked, $source),
                    ]),
                ]);
            }

            $locked->update([
                'status' => 'paid',
                'paid_at' => now(),
                'transaction_id' => $transaction?->id,
                'meta' => array_merge($locked->meta ?? [], [
                    'komerce_fulfilled_from' => $source,
                    'simulated_payment' => (bool) ($source['gateway_simulated'] ?? $source['simulated'] ?? false),
                ]),
            ]);
        });
    }

    private function formatPaymentOrder(PaymentOrder $order): array
    {
        $order->loadMissing([
            'package:id,name,price,duration_days,devices_quota',
            'application:id,name',
            'affiliate.user:id,name',
        ]);

        $meta = is_array($order->meta) ? $order->meta : [];
        $listPrice = (float) ($meta['list_price'] ?? $order->amount);
        $discountAmount = (float) ($meta['discount_amount'] ?? 0);
        $discountPercent = (float) ($meta['discount_percent'] ?? 0);
        $packageListPrice = (float) ($meta['package_list_price'] ?? $order->package?->price ?? 0);
        $slotLineAmount = (float) ($meta['slot_line_amount'] ?? 0);
        $extraSlots = (int) ($meta['extra_device_slots'] ?? $meta['additional_device_slots'] ?? 0);
        $totalDevices = (int) ($meta['total_devices_target'] ?? 0);
        $devicesQuota = (int) ($order->package?->devices_quota ?? 0);
        $durationDays = (int) ($order->package?->duration_days ?? 0);

        return [
            'order_id' => $order->order_id,
            'payment_id' => $order->komerce_payment_id,
            'status' => $order->status,
            'amount' => (float) $order->amount,
            'amount_label' => 'Rp '.number_format((float) $order->amount, 0, ',', '.'),
            'payment_type' => $order->payment_type,
            'qr_string' => $order->qr_string,
            'payment_page_url' => $order->payment_page_url,
            'expires_at' => $order->expires_at?->toIso8601String(),
            'expires_at_label' => $order->expires_at?->format('d M Y H:i'),
            'paid_at' => $order->paid_at?->toIso8601String(),
            'package' => $order->package ? [
                'id' => $order->package->id,
                'name' => $order->package->name,
                'price' => (float) $order->package->price,
                'duration_days' => $durationDays,
                'devices_quota' => $devicesQuota,
            ] : null,
            'application' => $order->application ? [
                'id' => $order->application->id,
                'name' => $order->application->name,
            ] : null,
            'simulate_available' => $this->allowsSimulatePayment() && $order->status === 'pending',
            'referral_code' => $order->referral_code,
            'affiliate_name' => $order->affiliate?->user?->name,
            'summary' => [
                'order_type' => $order->type,
                'order_type_label' => $this->paymentOrderTypeLabel($order->type),
                'list_price' => $listPrice,
                'list_price_label' => 'Rp '.number_format($listPrice, 0, ',', '.'),
                'package_list_price' => $packageListPrice,
                'package_list_price_label' => 'Rp '.number_format($packageListPrice, 0, ',', '.'),
                'slot_line_amount' => $slotLineAmount,
                'slot_line_amount_label' => 'Rp '.number_format($slotLineAmount, 0, ',', '.'),
                'extra_device_slots' => $extraSlots,
                'total_devices' => $totalDevices > 0 ? $totalDevices : ($devicesQuota + max(0, $extraSlots)),
                'devices_quota' => $devicesQuota,
                'duration_days' => $durationDays,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'discount_amount_label' => $discountAmount > 0
                    ? '−Rp '.number_format($discountAmount, 0, ',', '.')
                    : null,
            ],
        ];
    }

    private function paymentOrderTypeLabel(string $type): string
    {
        return match ($type) {
            'renew' => 'Perpanjang paket',
            'purchase' => 'Beli paket',
            'upgrade' => 'Upgrade paket',
            'device_slots' => 'Tambah slot perangkat',
            default => 'Pembayaran paket',
        };
    }

    public function allowsSimulatePayment(): bool
    {
        $configured = config('komerce.allow_simulate');

        if ($configured !== null) {
            return filter_var($configured, FILTER_VALIDATE_BOOLEAN);
        }

        return str_contains(strtolower((string) config('komerce.base_url')), 'sandbox');
    }

    public function snapshotGatewayMeta(PaymentOrder $order, array $source = []): array
    {
        $create = is_array($order->meta['komerce_create'] ?? null) ? $order->meta['komerce_create'] : [];
        $fulfill = $source ?: (is_array($order->meta['komerce_fulfilled_from'] ?? null) ? $order->meta['komerce_fulfilled_from'] : []);
        $simulated = (bool) (
            $source['gateway_simulated']
            ?? $source['simulated']
            ?? data_get($order->meta, 'simulated_payment', false)
        );

        $fulfillmentSource = match (true) {
            ! empty($source['gateway_simulated']) => 'sandbox_simulate',
            ! empty($source['simulated']) => 'local_simulate_legacy',
            (bool) $fulfill => 'webhook_or_poll',
            default => 'unknown',
        };

        $order->loadMissing(['affiliate.user:id,name']);
        $affiliateName = $order->affiliate?->user?->name ?? ($order->referral_code ? $order->affiliate?->code : null);

        return [
            'provider' => 'Komerce',
            'environment' => str_contains(strtolower((string) config('komerce.base_url')), 'sandbox') ? 'sandbox' : 'production',
            'softsale_order_id' => $order->order_id,
            'komerce_payment_id' => $order->komerce_payment_id,
            'external_id' => $create['external_id'] ?? null,
            'payment_status' => data_get($order->meta, 'komerce_status_sync.status')
                ?? $create['status']
                ?? strtoupper((string) $order->status),
            'amount' => (float) $order->amount,
            'payment_type' => $order->payment_type,
            'payment_url' => $order->payment_page_url,
            'qr_string' => $order->qr_string,
            'expired_at' => $order->expires_at?->toIso8601String(),
            'gateway_created_at' => $create['created_at'] ?? null,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'fulfillment_source' => $fulfillmentSource,
            'simulated' => $simulated,
            'referral_code' => $order->referral_code,
            'affiliate_name' => $affiliateName,
            'gateway_mismatch' => data_get($order->meta, 'gateway_mismatch'),
            'create_response' => $create,
            'fulfill_response' => $fulfill ?: data_get($order->meta, 'komerce_status_sync'),
        ];
    }

    private function generateOrderId(): string
    {
        do {
            $orderId = 'SS-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
        } while (PaymentOrder::query()->where('order_id', $orderId)->exists());

        return $orderId;
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
