<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ClientAuthService;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ClientSessionController extends Controller
{
    public function __construct(
        private ClientAuthService $authService,
        private LicenseService $licenseService,
    ) {}

    public function me(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'logged_in' => false,
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'client' || ! $user->client) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'logged_in' => false,
            ]);
        }

        return response()->json([
            'logged_in' => true,
            'client' => $this->authService->clientPayload($user),
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

        return response()->json([
            'transactions' => $transactions,
        ]);
    }

    private function formatClientTransaction(Transaction $transaction): array
    {
        $paidAt = $transaction->paid_at instanceof Carbon
            ? $transaction->paid_at
            : ($transaction->paid_at ? Carbon::parse($transaction->paid_at) : null);

        return [
            'id' => $transaction->id,
            'invoice' => $transaction->reference,
            'description' => $transaction->description,
            'amount' => (float) $transaction->amount,
            'amount_label' => 'Rp '.number_format((float) $transaction->amount, 0, ',', '.'),
            'type' => $transaction->type,
            'type_label' => match ($transaction->type) {
                'purchase' => 'Pembelian',
                'upgrade' => 'Upgrade',
                'renew' => 'Perpanjang',
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
        ];
    }
}
