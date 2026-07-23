<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminPaymentOrderController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $statusFilter = strtolower(trim((string) $request->query('status', '')));

        $query = PaymentOrder::query()
            ->with([
                'client:id,company_name,user_id',
                'client.user:id,name,email',
                'application:id,name',
                'transaction:id,reference',
            ])
            ->latest('created_at')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('komerce_payment_id', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($client) => $client->where('company_name', 'like', "%{$search}%"))
                    ->orWhereHas('client.user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
            });
        }

        if ($statusFilter !== '' && $statusFilter !== 'all') {
            if ($statusFilter === 'expired') {
                $query->where(function ($builder) {
                    $builder
                        ->where('status', 'expired')
                        ->orWhere(function ($pending) {
                            $pending
                                ->where('status', 'pending')
                                ->whereNotNull('expires_at')
                                ->where('expires_at', '<', now());
                        });
                });
            } elseif ($statusFilter === 'pending') {
                $query
                    ->where('status', 'pending')
                    ->where(function ($builder) {
                        $builder
                            ->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
            } else {
                $query->where('status', $statusFilter);
            }
        }

        $orders = $query->get()->map(function (PaymentOrder $order) {
            $synced = $this->paymentService->syncOrderFromGateway($order);

            return $this->formatOrder($synced, $synced->status);
        });

        $all = PaymentOrder::query()->get()->map(function (PaymentOrder $order) {
            return $this->paymentService->syncOrderFromGateway($order)->status;
        });

        return response()->json([
            'summary' => [
                'total_count' => $all->count(),
                'pending_count' => $all->filter(fn (string $status) => $status === 'pending')->count(),
                'paid_count' => $all->filter(fn (string $status) => $status === 'paid')->count(),
                'expired_count' => $all->filter(fn (string $status) => $status === 'expired')->count(),
            ],
            'orders' => $orders->values(),
        ]);
    }

    private function formatOrder(PaymentOrder $order, string $effectiveStatus): array
    {
        $createdAt = $order->created_at instanceof Carbon ? $order->created_at : Carbon::parse($order->created_at);
        $expiresAt = $order->expires_at instanceof Carbon
            ? $order->expires_at
            : ($order->expires_at ? Carbon::parse($order->expires_at) : null);

        $customerName = $order->client?->company_name ?: $order->client?->user?->name ?: '—';
        $gateway = $this->paymentService->snapshotGatewayMeta($order);
        $remoteStatus = strtoupper((string) data_get($order->meta, 'komerce_status_sync.status', ''));
        $hasMismatch = is_array(data_get($order->meta, 'gateway_mismatch'));

        return [
            'id' => $order->id,
            'order_id' => $order->order_id,
            'payment_id' => $order->komerce_payment_id,
            'customer_name' => $customerName,
            'customer_email' => $order->client?->user?->email,
            'application_name' => $order->application?->name,
            'amount' => (float) $order->amount,
            'amount_label' => $this->formatCurrency((float) $order->amount),
            'payment_type' => $order->payment_type,
            'payment_type_label' => $this->paymentTypeLabel($order->payment_type),
            'status' => $effectiveStatus,
            'status_label' => $this->statusLabel($effectiveStatus),
            'gateway_status' => $remoteStatus ?: null,
            'gateway_status_label' => $remoteStatus ? $this->statusLabel(strtolower($remoteStatus)) : null,
            'has_gateway_mismatch' => $hasMismatch,
            'created_at' => $createdAt->format('d M Y, h:i A'),
            'created_at_short' => $createdAt->format('d M Y, h:i A'),
            'expires_at' => $expiresAt?->format('d M Y, h:i A'),
            'paid_at' => $order->paid_at?->format('d M Y, h:i A'),
            'transaction_reference' => $order->transaction?->reference,
            'payment_page_url' => $order->payment_page_url,
            'has_gateway_details' => true,
            'gateway' => $this->presentGateway($gateway),
        ];
    }

    private function presentGateway(array $raw): array
    {
        $fulfillmentLabel = match ($raw['fulfillment_source'] ?? '') {
            'sandbox_simulate' => 'Simulasi Sandbox (Gateway)',
            'local_simulate_legacy' => 'Simulasi Lokal (Legacy — tidak sinkron gateway)',
            'webhook_or_poll' => 'Webhook / Polling Status',
            default => '—',
        };

        $summary = [
            ['label' => 'Provider', 'value' => $raw['provider'] ?? 'Komerce'],
            ['label' => 'Environment', 'value' => strtoupper((string) ($raw['environment'] ?? '—'))],
            ['label' => 'Order SoftSale', 'value' => $raw['softsale_order_id'] ?? '—'],
            ['label' => 'Payment ID Komerce', 'value' => $raw['komerce_payment_id'] ?? '—'],
            ['label' => 'External ID', 'value' => $raw['external_id'] ?? '—'],
            ['label' => 'Status Gateway', 'value' => $raw['payment_status'] ?? '—'],
            ['label' => 'Tipe Pembayaran', 'value' => strtoupper((string) ($raw['payment_type'] ?? '—'))],
            ['label' => 'Nominal', 'value' => isset($raw['amount']) ? $this->formatCurrency((float) $raw['amount']) : '—'],
            ['label' => 'Payment URL', 'value' => $raw['payment_url'] ?? '—'],
            ['label' => 'QR String', 'value' => $raw['qr_string'] ?? '—'],
            ['label' => 'Kedaluwarsa QRIS', 'value' => $this->formatIsoLabel($raw['expired_at'] ?? null)],
            ['label' => 'Dibuat (Gateway)', 'value' => $this->formatIsoLabel($raw['gateway_created_at'] ?? null)],
            ['label' => 'Dibayar', 'value' => $this->formatIsoLabel($raw['paid_at'] ?? null)],
            ['label' => 'Sumber Konfirmasi', 'value' => $fulfillmentLabel],
            ['label' => 'Simulasi', 'value' => ! empty($raw['simulated']) ? 'Ya' : 'Tidak'],
        ];

        return [
            'has_details' => true,
            'summary' => $summary,
            'create_response' => $raw['create_response'] ?? [],
            'fulfill_response' => $raw['fulfill_response'] ?? [],
        ];
    }

    private function formatIsoLabel(?string $value): string
    {
        if (! $value) {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('d M Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function paymentTypeLabel(string $type): string
    {
        return match (strtolower($type)) {
            'qris' => 'QRIS',
            'bank_transfer', 'va' => 'Bank Transfer',
            default => strtoupper($type),
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'canceled' => 'Canceled',
            default => ucfirst($status),
        };
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
