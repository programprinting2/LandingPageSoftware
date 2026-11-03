<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientCheckoutRequest;
use App\Http\Requests\UpdateClientAffiliateCodeRequest;
use App\Http\Requests\UpdateClientProfileRequest;
use App\Models\Transaction;
use App\Services\AffiliateService;
use App\Services\ClientAuthService;
use App\Services\LicenseService;
use App\Services\ClientCommerceService;
use App\Services\PaymentService;
use App\Services\ReportArchivePasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ClientSessionController extends Controller
{
    public function __construct(
        private ClientAuthService $authService,
        private LicenseService $licenseService,
        private AffiliateService $affiliateService,
        private PaymentService $paymentService,
        private ClientCommerceService $commerceService,
        private ReportArchivePasswordService $reportArchivePassword,
    ) {}

    public function updateProfile(UpdateClientProfileRequest $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->authService->canAccessClientPortal($user)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $result = $this->authService->updateClientProfile($user, $request->validated());
        $code = ($result['status'] ?? '') === 'success' ? 200 : 422;

        return response()->json($result, $code);
    }

    public function me(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'logged_in' => false,
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->authService->canAccessClientPortal($user)) {
            // Jangan hapus session admin/dll. — hanya client nonaktif yang perlu dipaksa logout.
            if ($user->role === 'client') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json([
                'logged_in' => false,
            ]);
        }

        return response()->json([
            'logged_in' => true,
            'client' => $this->authService->clientPayload($user),
        ]);
    }

    public function affiliate(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Login diperlukan.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 10);

        return response()->json($this->affiliateService->dashboardPayload($user, $page, $perPage));
    }

    public function enrollAffiliate(): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Login diperlukan.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('client');

        if (! $this->authService->canAccessClientPortal($user)) {
            return response()->json([
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        if ($this->affiliateService->isAffiliatePortalActive($user)) {
            return response()->json([
                'message' => 'Program affiliate sudah aktif.',
                'affiliate' => $this->affiliateService->affiliateSummaryPayload($user),
            ]);
        }

        $affiliate = $this->affiliateService->enrollClient($user);

        return response()->json([
            'message' => 'Program affiliate berhasil diaktifkan.',
            'affiliate' => $this->affiliateService->affiliateSummaryPayload($user->fresh()),
            'dashboard' => $this->affiliateService->dashboardPayload($user->fresh()),
        ], 201);
    }

    public function updateAffiliateCode(UpdateClientAffiliateCodeRequest $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Login diperlukan.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->authService->canAccessClientPortal($user)) {
            return response()->json([
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        $user->loadMissing('affiliate');
        $before = $user->affiliate?->code;

        try {
            $affiliate = $this->affiliateService->updateClientAffiliateCode($user, $request->validated('code'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?? 'Kode tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = $user->fresh();

        $message = ($before ?? '') === $affiliate->code
            ? 'Kode referral tidak berubah.'
            : 'Kode referral diperbarui. Link dan voucher lama tidak berlaku lagi.';

        return response()->json([
            'message' => $message,
            'affiliate' => $this->affiliateService->affiliateSummaryPayload($user),
        ]);
    }

    public function license(): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Login diperlukan.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('client');

        if ($user->role !== 'client' || ! $user->client) {
            return response()->json([
                'message' => 'Akun ini bukan client.',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'license' => $this->licenseService->listForUser($user),
        ]);
    }

    public function deactivateLicense(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Login diperlukan.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('client');

        if ($user->role !== 'client' || ! $user->client) {
            return response()->json([
                'message' => 'Akun ini bukan client.',
            ], 403);
        }

        $programId = (string) $request->input('program_id', '');
        $deviceId = (int) $request->input('device_id', 0);

        if ($deviceId > 0) {
            $result = $this->licenseService->deactivateById($user, $deviceId);
        } else {
            $result = $this->licenseService->deactivate($user, $programId);
        }

        $code = ($result['status'] ?? '') === 'success' ? 200 : 400;

        return response()->json($result, $code);
    }

    public function transactions(): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'Login diperlukan.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('client');

        if ($user->role !== 'client' || ! $user->client) {
            return response()->json([
                'message' => 'Akun ini bukan client.',
            ], 403);
        }

        $transactions = Transaction::query()
            ->where('client_id', $user->client->id)
            ->with([
                'application:id,name',
                'package:id,name',
            ])
            ->latest('paid_at')
            ->latest('id')
            ->get()
            ->map(fn (Transaction $transaction) => $this->formatClientTransaction($transaction));

        $pendingOrders = $this->commerceService->pendingOrdersForUser($user);

        return response()->json([
            'transactions' => $transactions,
            'pending_orders' => $pendingOrders,
        ]);
    }

    public function commercePlans(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $slug = (string) $request->query('application_slug', config('softsale.default_application_slug', 'spot-color'));

        $application = \App\Models\Application::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $application) {
            return response()->json(['message' => 'Aplikasi tidak ditemukan.'], 404);
        }

        $cms = app(\App\Services\LandingContentService::class)->all($application->id);
        $plans = app(\App\Services\LandingPricingService::class)
            ->buildPlans($application, $cms['pricing'] ?? []);

        $pendingOrder = null;
        foreach ($this->commerceService->pendingOrdersForUser($user, reconcileStale: false) as $order) {
            if (($order['application_slug'] ?? '') === $application->slug && ($order['is_payable'] ?? false)) {
                $pendingOrder = $order;
                break;
            }
        }

        return response()->json([
            'plans' => $this->commerceService->planCtasForApplication($user, $application, $plans),
            'pending_order' => $pendingOrder,
        ]);
    }

    public function activateTrial(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $slug = (string) $request->input('application_slug', config('softsale.default_application_slug', 'spot-color'));

        $result = $this->commerceService->activateTrial($user, $slug);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 422;

        return response()->json($result, $code);
    }

    private function formatClientTransaction(Transaction $transaction): array
    {
        $paidAt = $transaction->paid_at instanceof Carbon
            ? $transaction->paid_at
            : ($transaction->paid_at ? Carbon::parse($transaction->paid_at) : null);

        return [
            'id' => $transaction->id,
            'invoice' => $transaction->reference,
            'description' => $this->commerceService->billingTransactionDescription($transaction),
            'amount' => (float) $transaction->amount,
            'amount_label' => 'Rp '.number_format((float) $transaction->amount, 0, ',', '.'),
            'type' => $transaction->type,
            'type_label' => match ($transaction->type) {
                'purchase' => 'Pembelian',
                'upgrade' => 'Upgrade',
                'renew' => 'Perpanjang',
                'device_slots' => 'Tambah device',
                default => ucfirst($transaction->type),
            },
            'payment_method_label' => match ($transaction->payment_method) {
                'qris' => 'QRIS',
                'admin' => 'Admin',
                default => strtoupper((string) $transaction->payment_method),
            },
            'status' => $transaction->status,
            'status_label' => match ($transaction->status) {
                'paid' => 'Paid',
                'pending' => 'Pending',
                'failed' => 'Failed',
                'refunded' => 'Refunded',
                default => ucfirst($transaction->status),
            },
            'paid_at' => $paidAt?->format('d M Y'),
            'paid_at_iso' => $paidAt?->toIso8601String(),
        ];
    }

    public function checkout(StoreClientCheckoutRequest $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->authService->canAccessClientPortal($user)) {
            return response()->json(['message' => 'Akun ini bukan client.'], 403);
        }

        $validated = $request->validated();
        $slug = (string) ($validated['application_slug'] ?? config('softsale.default_application_slug', 'spot-color'));
        $referral = isset($validated['referral_code']) ? (string) $validated['referral_code'] : null;

        $totalDevices = isset($validated['total_devices']) ? (int) $validated['total_devices'] : null;

        $result = $this->commerceService->beginCheckout(
            $user,
            (int) $validated['package_id'],
            $slug,
            $referral,
            $totalDevices,
        );

        $code = ($result['status'] ?? '') === 'success' ? 200 : 422;

        return response()->json($result, $code);
    }

    public function checkoutDeviceSlots(\App\Http\Requests\StoreClientDeviceSlotCheckoutRequest $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->authService->canAccessClientPortal($user)) {
            return response()->json(['message' => 'Akun ini bukan client.'], 403);
        }

        $validated = $request->validated();
        $referral = isset($validated['referral_code']) ? (string) $validated['referral_code'] : null;

        $result = $this->commerceService->beginDeviceSlotCheckout(
            $user,
            (string) $validated['application_slug'],
            (int) $validated['additional_slots'],
            $referral,
        );

        $code = ($result['status'] ?? '') === 'success' ? 200 : 422;

        return response()->json($result, $code);
    }

    public function checkoutStatus(string $orderId): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $result = $this->paymentService->getOrderStatus($user, $orderId);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 422;

        return response()->json($result, $code);
    }

    public function checkoutSimulate(string $orderId): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $result = $this->paymentService->simulatePayment($user, $orderId);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 422;

        return response()->json($result, $code);
    }

    public function reportArchiveSettings(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->authService->canAccessClientPortal($user)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $applicationSlug = trim((string) ($request->query('application_slug') ?? $request->input('application_slug', '')));
        if ($applicationSlug === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'application_slug wajib diisi.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            ...$this->reportArchivePassword->settingsPayload($user, $applicationSlug),
        ]);
    }

    public function updateReportArchivePassword(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Login diperlukan.'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->authService->canAccessClientPortal($user)) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $applicationSlug = trim((string) ($request->input('application_slug') ?? $request->query('application_slug', '')));
        if ($applicationSlug === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'application_slug wajib diisi.',
            ], 422);
        }

        $password = (string) $request->input('password', '');
        $result = $this->reportArchivePassword->updatePassword($user, $password, $applicationSlug);
        $code = ($result['status'] ?? '') === 'success' ? 200 : 422;

        return response()->json($result, $code);
    }
}
