<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\Client;
use App\Models\LicenseDevice;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminLicenseController extends Controller
{
    public function index(): JsonResponse
    {
        $now = now();

        $rows = DB::table('client_applications as ca')
            ->join('clients as c', 'c.id', '=', 'ca.client_id')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->join('applications as a', 'a.id', '=', 'ca.application_id')
            ->leftJoin('application_packages as p', 'p.id', '=', 'ca.application_package_id')
            ->select([
                'ca.id',
                'ca.client_id',
                'ca.application_id',
                'ca.application_package_id',
                'ca.status',
                'ca.purchased_at',
                'ca.expires_at',
                'ca.devices_max',
                'c.company_name',
                'u.email',
                'a.name as application_name',
                'a.slug as application_slug',
                'p.name as package_name',
                'p.duration_days as package_duration_days',
            ])
            ->orderByDesc('ca.purchased_at')
            ->orderByDesc('ca.id')
            ->get()
            ->map(fn ($row) => $this->formatLicenseRow($row, $now));

        return response()->json([
            'licenses' => $rows,
        ]);
    }

    public function renew(Request $request, int $clientApplication): JsonResponse
    {
        $record = DB::table('client_applications')->where('id', $clientApplication)->first();

        if (! $record) {
            return response()->json([
                'message' => 'Lisensi tidak ditemukan.',
            ], 404);
        }

        $package = $record->application_package_id
            ? ApplicationPackage::find($record->application_package_id)
            : null;

        $durationDays = (int) ($package?->duration_days ?? 30);
        $purchasedAt = now();
        $expiresAt = $durationDays > 0 ? $purchasedAt->copy()->addDays($durationDays) : null;

        DB::table('client_applications')->where('id', $clientApplication)->update([
            'status' => 'active',
            'purchased_at' => $purchasedAt,
            'expires_at' => $expiresAt,
            'updated_at' => now(),
        ]);

        $client = Client::find($record->client_id);
        $application = Application::find($record->application_id);

        if ($client && $application && $package) {
            app(TransactionService::class)->recordRenew($client, $application, $package, 'admin', [
                'client_application_id' => (int) $clientApplication,
            ]);
        }

        return response()->json([
            'message' => 'Lisensi berhasil diperpanjang.',
        ]);
    }

    public function revoke(int $clientApplication): JsonResponse
    {
        $record = DB::table('client_applications')->where('id', $clientApplication)->first();

        if (! $record) {
            return response()->json([
                'message' => 'Lisensi tidak ditemukan.',
            ], 404);
        }

        $client = Client::find($record->client_id);

        DB::transaction(function () use ($record, $client) {
            DB::table('client_applications')->where('id', $record->id)->update([
                'status' => 'expired',
                'updated_at' => now(),
            ]);

            if ($client) {
                LicenseDevice::query()
                    ->where('user_id', $client->user_id)
                    ->where('application_id', $record->application_id)
                    ->delete();
            }
        });

        return response()->json([
            'message' => 'Lisensi berhasil dicabut.',
        ]);
    }

    private function formatLicenseRow(object $row, Carbon $now): array
    {
        $purchasedAt = $row->purchased_at ? Carbon::parse($row->purchased_at) : null;
        $expiresAt = $row->expires_at ? Carbon::parse($row->expires_at) : null;
        $isExpired = $expiresAt ? $now->greaterThan($expiresAt) : false;
        $displayStatus = $isExpired ? 'expired' : (string) $row->status;

        $packageName = $row->package_name ?: '—';
        $prefix = strtoupper(Str::substr(Str::slug($packageName, ''), 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = strtoupper(Str::substr((string) $row->application_slug, 0, 3)) ?: 'LIC';
        }

        $licenseKey = sprintf(
            '%s-%s-%04d',
            $prefix,
            ($purchasedAt ?? $now)->format('Y'),
            (int) $row->id
        );

        return [
            'id' => (int) $row->id,
            'license_key' => $licenseKey,
            'client_id' => (int) $row->client_id,
            'client_name' => $row->company_name,
            'client_email' => $row->email,
            'application_id' => (int) $row->application_id,
            'application_name' => $row->application_name,
            'application_slug' => $row->application_slug,
            'package_name' => $packageName,
            'package_id' => $row->application_package_id ? (int) $row->application_package_id : null,
            'devices_max' => (int) ($row->devices_max ?? 0),
            'status' => $displayStatus,
            'is_expired' => $isExpired,
            'purchased_at' => $purchasedAt?->format('d M Y'),
            'expires_at' => $expiresAt ? $expiresAt->format('d M Y') : 'Unlimited',
            'expires_at_raw' => $expiresAt?->toIso8601String(),
        ];
    }
}
