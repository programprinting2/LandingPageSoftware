<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminAffiliateRequest;
use App\Http\Requests\UpdateAdminAffiliateRequest;
use App\Models\Affiliate;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Services\ClientCommerceService;
use App\Http\Controllers\Concerns\PaginatesAdminPaymentOrderList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminAffiliateController extends Controller
{
    use PaginatesAdminPaymentOrderList;

    public function __construct(
        private ClientCommerceService $commerceService,
    ) {}

    public function index(): JsonResponse
    {
        $affiliates = Affiliate::query()
            ->with(['user:id,name,email'])
            ->latest()
            ->get()
            ->map(fn (Affiliate $affiliate) => $this->formatAffiliate($affiliate));

        return response()->json([
            'affiliates' => $affiliates,
        ]);
    }

    public function store(StoreAdminAffiliateRequest $request): JsonResponse
    {
        throw ValidationException::withMessages([
            'email' => 'Affiliate baru diaktifkan dari menu User (profil client). Buka User → Edit client → tab Affiliate.',
        ]);
    }

    public function update(UpdateAdminAffiliateRequest $request, Affiliate $affiliate): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($affiliate, $validated) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $affiliate->user->update($userData);

            $affiliate->update([
                'code' => Str::upper($validated['code']),
                'whatsapp' => $validated['whatsapp'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                'bank_account_name' => $validated['bank_account_name'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return response()->json([
            'message' => 'Affiliate berhasil diperbarui.',
            'affiliate' => $this->formatAffiliate($affiliate->fresh()),
        ]);
    }

    public function destroy(Affiliate $affiliate): JsonResponse
    {
        DB::transaction(function () use ($affiliate) {
            $user = $affiliate->user;
            $affiliate->delete();
            $user?->delete();
        });

        return response()->json([
            'message' => 'Affiliate berhasil dihapus.',
        ]);
    }

    public function transactions(Request $request, Affiliate $affiliate): JsonResponse
    {
        $affiliate->loadMissing('user:id,name,email');

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min((int) $request->query('per_page', 20), 50));
        $statusFilter = $this->normalizeAdminTrxStatusFilter($request->query('status'));

        $listQuery = PaymentOrder::query()
            ->where('affiliate_id', $affiliate->id)
            ->with([
                'client:id,company_name,user_id',
                'client.user:id,name,email',
                'application:id,name',
                'package:id,name,price,affiliate_commission_fixed,affiliate_commission_percent',
                'transaction:id,reference,status,paid_at,meta',
            ]);

        $this->applyAdminTrxStatusFilter($listQuery, $statusFilter);

        $paginator = $listQuery
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'affiliate' => [
                'id' => $affiliate->id,
                'code' => $affiliate->code,
                'name' => $affiliate->user?->name,
                'email' => $affiliate->user?->email,
            ],
            'summary' => $this->affiliateOrderSummary($affiliate->id),
            'status_filter' => $statusFilter,
            'transactions' => $paginator->getCollection()
                ->map(fn (PaymentOrder $order) => $this->formatAffiliateTransaction($order))
                ->values(),
            'pagination' => $this->paginationPayload($paginator),
        ]);
    }

    private function affiliateOrderSummary(int $affiliateId): array
    {
        $base = PaymentOrder::query()->where('affiliate_id', $affiliateId);
        $paidAmount = (float) (clone $base)->where('status', 'paid')->sum('amount');

        $paidOrders = (clone $base)
            ->where('status', 'paid')
            ->with([
                'package:id,name,price,affiliate_commission_fixed,affiliate_commission_percent',
                'transaction:id,meta',
            ])
            ->get();

        $commissionTotal = (float) $paidOrders->sum(
            fn (PaymentOrder $order) => $this->commissionAmountForOrder($order)
        );

        return [
            'total_count' => (clone $base)->count(),
            'paid_count' => (clone $base)->where('status', 'paid')->count(),
            'paid_amount' => $paidAmount,
            'paid_amount_label' => $this->formatCurrency($paidAmount),
            'pending_count' => (clone $base)->where('status', 'pending')->count(),
            'commission_total' => $commissionTotal,
            'commission_total_label' => $this->formatCurrency($commissionTotal),
        ];
    }

    private function commissionAmountForOrder(PaymentOrder $order): float
    {
        $commissionAmount = (float) data_get($order->transaction?->meta, 'commission_amount', 0);

        if ($commissionAmount <= 0 && $order->package && $order->status === 'paid') {
            $commissionAmount = $order->package->calculateAffiliateCommission((float) $order->amount);
        }

        return $commissionAmount;
    }

    private function resolveAffiliateCode(?string $code, string $name): string
    {
        $normalized = Str::upper(trim((string) $code));

        if ($normalized !== '' && ! Affiliate::query()->where('code', $normalized)->exists()) {
            return $normalized;
        }

        return $this->generateAffiliateCode($name);
    }

    private function generateAffiliateCode(string $name): string
    {
        $letter = $this->firstNameInitial($name);
        $prefix = 'A'.$letter;

        $maxSequence = Affiliate::query()
            ->where('code', 'like', $prefix.'____')
            ->pluck('code')
            ->reduce(function (int $max, string $existingCode) use ($prefix) {
                if (! preg_match('/^'.preg_quote($prefix, '/').'(\d{4})$/', $existingCode, $matches)) {
                    return $max;
                }

                return max($max, (int) $matches[1]);
            }, 0);

        return $prefix.str_pad((string) ($maxSequence + 1), 4, '0', STR_PAD_LEFT);
    }

    private function firstNameInitial(string $name): string
    {
        $firstName = Str::of($name)->trim()->explode(' ')->first() ?? '';
        $initial = Str::upper(Str::substr($firstName, 0, 1));

        return preg_match('/^[A-Z]$/', $initial) ? $initial : 'X';
    }

    private function formatAffiliate(Affiliate $affiliate): array
    {
        $affiliate->loadMissing('user:id,name,email');

        return [
            'id' => $affiliate->id,
            'name' => $affiliate->user?->name,
            'email' => $affiliate->user?->email,
            'code' => $affiliate->code,
            'whatsapp' => $affiliate->whatsapp,
            'bank_name' => $affiliate->bank_name,
            'bank_account' => $affiliate->bank_account,
            'bank_account_name' => $affiliate->bank_account_name,
            'status' => $affiliate->status,
            'sales_count' => (int) $affiliate->sales_count,
            'total_commission' => (float) $affiliate->total_commission,
            'pending_commission' => (float) $affiliate->pending_commission,
            'total_commission_label' => $this->formatCurrency((float) $affiliate->total_commission),
            'pending_commission_label' => $this->formatCurrency((float) $affiliate->pending_commission),
            'created_at' => $affiliate->created_at?->format('d M Y'),
        ];
    }

    private function formatAffiliateTransaction(PaymentOrder $order): array
    {
        $customerName = $order->client?->company_name ?: $order->client?->user?->name ?: '—';
        $createdAt = $order->created_at instanceof Carbon ? $order->created_at : Carbon::parse($order->created_at);
        $paidAt = $order->paid_at instanceof Carbon
            ? $order->paid_at
            : ($order->paid_at ? Carbon::parse($order->paid_at) : null);

        $commissionAmount = $this->commissionAmountForOrder($order);

        return [
            'id' => $order->id,
            'order_id' => $order->order_id,
            'payment_id' => $order->komerce_payment_id,
            'reference' => $order->transaction?->reference ?? $order->order_id,
            'referral_code' => $order->referral_code,
            'customer_name' => $customerName,
            'description' => $this->commerceService->billingOrderDescription($order),
            'application_name' => $order->application?->name,
            'package_name' => $order->package?->name,
            'type' => $order->type,
            'type_label' => $this->transactionTypeLabel((string) $order->type),
            'amount' => (float) $order->amount,
            'amount_label' => $this->formatCurrency((float) $order->amount),
            'commission_amount' => $commissionAmount,
            'commission_label' => $commissionAmount > 0
                ? $this->formatCurrency($commissionAmount)
                : ($order->package?->affiliateCommissionLabel((float) $order->amount) ?? '—'),
            'status' => $order->status,
            'status_label' => $this->transactionStatusLabel((string) $order->status),
            'created_at' => $createdAt->format('d M Y H:i'),
            'paid_at' => $paidAt?->format('d M Y H:i'),
            'paid_at_date' => $paidAt?->format('d M Y'),
        ];
    }

    private function transactionTypeLabel(string $type): string
    {
        return match ($type) {
            'purchase' => 'Pembelian',
            'upgrade' => 'Upgrade',
            'renew' => 'Perpanjang',
            default => $type !== '' ? ucfirst($type) : '—',
        };
    }

    private function transactionStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'canceled' => 'Canceled',
            default => $status !== '' ? ucfirst($status) : '—',
        };
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
