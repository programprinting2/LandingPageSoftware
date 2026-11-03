<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminPromoVoucherRequest;
use App\Http\Requests\UpdateAdminPromoVoucherRequest;
use App\Models\Application;
use App\Models\PaymentOrder;
use App\Models\PromoVoucher;
use App\Support\AffiliateReferralCode;
use App\Services\ClientCommerceService;
use App\Http\Controllers\Concerns\PaginatesAdminPaymentOrderList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminPromoVoucherController extends Controller
{
    use PaginatesAdminPaymentOrderList;

    public function __construct(
        private ClientCommerceService $commerceService,
    ) {}

    public function index(): JsonResponse
    {
        $vouchers = PromoVoucher::query()
            ->with('application:id,name,slug')
            ->withCount([
                'paymentOrders as paid_usage_count' => fn ($q) => $q->where('status', 'paid'),
            ])
            ->latest('id')
            ->get()
            ->map(fn (PromoVoucher $voucher) => $this->formatVoucher($voucher));

        return response()->json([
            'vouchers' => $vouchers,
        ]);
    }

    public function store(StoreAdminPromoVoucherRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $code = AffiliateReferralCode::validate($validated['code']);

        $voucher = PromoVoucher::create([
            'code' => $code['code'],
            'label' => $validated['label'] ?? null,
            'application_id' => $validated['application_id'] ?? null,
            'discount_percent' => (float) $validated['discount_percent'],
            'status' => $validated['status'],
        ]);

        $voucher->load('application:id,name,slug');
        $voucher->loadCount([
            'paymentOrders as paid_usage_count' => fn ($q) => $q->where('status', 'paid'),
        ]);

        return response()->json([
            'message' => 'Voucher promo berhasil dibuat.',
            'voucher' => $this->formatVoucher($voucher),
        ], 201);
    }

    public function update(UpdateAdminPromoVoucherRequest $request, PromoVoucher $promoVoucher): JsonResponse
    {
        $validated = $request->validated();
        $code = AffiliateReferralCode::validate($validated['code'], null, $promoVoucher->id);

        $promoVoucher->update([
            'code' => $code['code'],
            'label' => $validated['label'] ?? null,
            'application_id' => $validated['application_id'] ?? null,
            'discount_percent' => (float) $validated['discount_percent'],
            'status' => $validated['status'],
        ]);

        $promoVoucher->load('application:id,name,slug');
        $promoVoucher->loadCount([
            'paymentOrders as paid_usage_count' => fn ($q) => $q->where('status', 'paid'),
        ]);

        return response()->json([
            'message' => 'Voucher promo diperbarui.',
            'voucher' => $this->formatVoucher($promoVoucher->fresh()),
        ]);
    }

    public function destroy(PromoVoucher $promoVoucher): JsonResponse
    {
        $promoVoucher->delete();

        return response()->json([
            'message' => 'Voucher promo dihapus.',
        ]);
    }

    public function usages(Request $request, PromoVoucher $promoVoucher): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min((int) $request->query('per_page', 20), 50));
        $statusFilter = $this->normalizeAdminTrxStatusFilter($request->query('status'));

        $listQuery = PaymentOrder::query()
            ->where('promo_voucher_id', $promoVoucher->id)
            ->with([
                'client:id,company_name,user_id',
                'client.user:id,name,email',
                'application:id,name',
                'package:id,name',
                'transaction:id,reference,status,paid_at',
            ]);

        $this->applyAdminTrxStatusFilter($listQuery, $statusFilter);

        $paginator = $listQuery
            ->latest('paid_at')
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'voucher' => $this->formatVoucher($promoVoucher->load('application:id,name,slug')),
            'summary' => $this->promoVoucherUsageSummary($promoVoucher->id),
            'status_filter' => $statusFilter,
            'usages' => $paginator->getCollection()
                ->map(fn (PaymentOrder $order) => $this->formatUsageRow($order))
                ->values()
                ->all(),
            'pagination' => $this->paginationPayload($paginator),
        ]);
    }

    private function promoVoucherUsageSummary(int $promoVoucherId): array
    {
        $base = PaymentOrder::query()->where('promo_voucher_id', $promoVoucherId);
        $paidAmount = (float) (clone $base)->where('status', 'paid')->sum('amount');

        return [
            'total_count' => (clone $base)->count(),
            'paid_count' => (clone $base)->where('status', 'paid')->count(),
            'pending_count' => (clone $base)->where('status', 'pending')->count(),
            'paid_amount' => $paidAmount,
            'paid_amount_label' => $this->formatCurrency($paidAmount),
        ];
    }

    private function formatVoucher(PromoVoucher $voucher): array
    {
        return [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'label' => $voucher->label,
            'application_id' => $voucher->application_id,
            'application_name' => $voucher->application?->name,
            'application_scope_label' => $voucher->application_id
                ? ($voucher->application?->name ?? '—')
                : 'Semua aplikasi',
            'discount_percent' => (float) $voucher->discount_percent,
            'discount_percent_label' => $this->formatPercent((float) $voucher->discount_percent),
            'status' => $voucher->status,
            'paid_usage_count' => (int) ($voucher->paid_usage_count ?? 0),
            'created_at' => $voucher->created_at?->format('d M Y'),
        ];
    }

    private function formatUsageRow(PaymentOrder $order): array
    {
        $customerName = $order->client?->company_name ?: $order->client?->user?->name ?: '—';
        $paidAt = $order->paid_at instanceof Carbon
            ? $order->paid_at
            : ($order->paid_at ? Carbon::parse($order->paid_at) : null);

        return [
            'id' => $order->id,
            'order_id' => $order->order_id,
            'reference' => $order->transaction?->reference ?? $order->order_id,
            'voucher_code' => $order->referral_code,
            'customer_name' => $customerName,
            'description' => $this->commerceService->billingOrderDescription($order),
            'application_name' => $order->application?->name,
            'package_name' => $order->package?->name,
            'type' => $order->type,
            'type_label' => match ($order->type) {
                'purchase' => 'Pembelian',
                'upgrade' => 'Upgrade',
                'renew' => 'Perpanjang',
                'device_slots' => 'Slot perangkat',
                default => ucfirst((string) $order->type),
            },
            'amount' => (float) $order->amount,
            'amount_label' => $this->formatCurrency((float) $order->amount),
            'discount_amount_label' => $this->formatCurrency((float) data_get($order->meta, 'discount_amount', 0)),
            'status' => $order->status,
            'status_label' => match ($order->status) {
                'paid' => 'Paid',
                'pending' => 'Pending',
                default => ucfirst((string) $order->status),
            },
            'paid_at_date' => $paidAt?->format('d M Y') ?? '—',
        ];
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function formatPercent(float $value): string
    {
        $rounded = round($value, 2);

        return (fmod($rounded, 1.0) === 0.0 ? (string) (int) $rounded : (string) $rounded).'%';
    }
}
