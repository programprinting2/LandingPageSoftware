<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\PaymentOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminCommissionController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = PaymentOrder::query()
            ->whereNotNull('affiliate_id')
            ->where('status', 'paid')
            ->with([
                'affiliate:id,code,user_id',
                'affiliate.user:id,name',
                'client:id,company_name,user_id',
                'client.user:id,name',
                'application:id,name',
                'package:id,name,price,affiliate_commission_fixed,affiliate_commission_percent',
                'transaction:id,reference,status,paid_at,meta',
            ])
            ->latest('paid_at')
            ->latest('id')
            ->get();

        $commissions = $orders
            ->map(fn (PaymentOrder $order) => $this->formatCommission($order))
            ->filter(fn (array $row) => $row['commission_amount'] > 0)
            ->values();

        $paidOut = $commissions->where('payout_status', 'paid');
        $pending = $commissions->where('payout_status', 'pending');

        return response()->json([
            'summary' => [
                'total_paid_out' => (float) $paidOut->sum('commission_amount'),
                'total_paid_out_label' => $this->formatCurrency((float) $paidOut->sum('commission_amount')),
                'pending_payout' => (float) $pending->sum('commission_amount'),
                'pending_payout_label' => $this->formatCurrency((float) $pending->sum('commission_amount')),
                'total_count' => $commissions->count(),
            ],
            'commissions' => $commissions,
        ]);
    }

    public function pay(PaymentOrder $paymentOrder): JsonResponse
    {
        if (! $paymentOrder->affiliate_id || $paymentOrder->status !== 'paid') {
            return response()->json([
                'message' => 'Komisi tidak ditemukan atau transaksi belum lunas.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($paymentOrder) {
                $order = PaymentOrder::query()
                    ->lockForUpdate()
                    ->with([
                        'affiliate',
                        'package:id,name,price,affiliate_commission_fixed,affiliate_commission_percent',
                        'transaction',
                    ])
                    ->find($paymentOrder->id);

                if (! $order || ! $order->affiliate || ! $order->transaction) {
                    throw new \RuntimeException('Data komisi tidak lengkap.');
                }

                if (data_get($order->transaction->meta, 'commission_paid_at')) {
                    throw new \RuntimeException('Komisi sudah dibayar.');
                }

                $commissionAmount = $this->resolveCommissionAmount($order);

                if ($commissionAmount <= 0) {
                    throw new \RuntimeException('Nominal komisi tidak valid.');
                }

                $affiliate = Affiliate::query()->lockForUpdate()->find($order->affiliate_id);

                if (! $affiliate) {
                    throw new \RuntimeException('Affiliate tidak ditemukan.');
                }

                $order->transaction->update([
                    'meta' => array_merge($order->transaction->meta ?? [], [
                        'commission_paid_at' => now()->toIso8601String(),
                    ]),
                ]);

                $pending = (float) $affiliate->pending_commission;
                $deduct = min($pending, $commissionAmount);

                if ($deduct > 0) {
                    $affiliate->decrement('pending_commission', $deduct);
                }

                $affiliate->increment('total_commission', $commissionAmount);
            });
        } catch (\RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $paymentOrder->refresh();

        return response()->json([
            'message' => 'Komisi affiliate berhasil ditandai dibayar.',
            'commission' => $this->formatCommission(
                $paymentOrder->loadMissing([
                    'affiliate.user:id,name',
                    'client.user:id,name',
                    'application:id,name',
                    'package:id,name,price,affiliate_commission_fixed,affiliate_commission_percent',
                    'transaction:id,reference,status,paid_at,meta',
                ])
            ),
        ]);
    }

    private function formatCommission(PaymentOrder $order): array
    {
        $order->loadMissing([
            'affiliate.user:id,name',
            'client.user:id,name',
            'application:id,name',
            'package:id,name,price,affiliate_commission_fixed,affiliate_commission_percent',
            'transaction:id,reference,status,paid_at,meta',
        ]);

        $commissionAmount = $this->resolveCommissionAmount($order);
        $paidAt = $order->paid_at instanceof Carbon
            ? $order->paid_at
            : ($order->paid_at ? Carbon::parse($order->paid_at) : null);
        $commissionPaidAt = data_get($order->transaction?->meta, 'commission_paid_at');
        $payoutStatus = $commissionPaidAt ? 'paid' : 'pending';
        $customerName = $order->client?->company_name ?: $order->client?->user?->name ?: '—';

        return [
            'id' => $order->id,
            'order_id' => $order->order_id,
            'affiliate_id' => $order->affiliate_id,
            'affiliate_code' => $order->affiliate?->code ?? $order->referral_code ?? '—',
            'affiliate_name' => $order->affiliate?->user?->name,
            'client_name' => $customerName,
            'package_name' => $order->package?->name ?? '—',
            'commission_amount' => $commissionAmount,
            'commission_label' => $this->formatCurrency($commissionAmount),
            'date' => $paidAt?->format('d M Y') ?? '—',
            'paid_at' => $paidAt?->format('d M Y H:i'),
            'payout_status' => $payoutStatus,
            'payout_status_label' => $payoutStatus === 'paid' ? 'Paid' : 'Pending',
            'commission_paid_at' => $commissionPaidAt
                ? Carbon::parse($commissionPaidAt)->format('d M Y H:i')
                : null,
        ];
    }

    private function resolveCommissionAmount(PaymentOrder $order): float
    {
        $commissionAmount = (float) data_get($order->transaction?->meta, 'commission_amount', 0);

        if ($commissionAmount <= 0 && $order->package) {
            $commissionAmount = $order->package->calculateAffiliateCommission((float) $order->amount);
        }

        return round($commissionAmount, 2);
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
