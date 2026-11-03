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
    public function index(Request $request): JsonResponse
    {
        $now = now();
        $applicationId = (int) $request->query('application_id', 0);
        $search = trim((string) $request->query('q', ''));

        $query = DB::table('client_applications as ca')
            ->join('clients as c', 'c.id', '=', 'ca.client_id')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->join('applications as a', 'a.id', '=', 'ca.application_id')
            ->leftJoin('application_packages as p', 'p.id', '=', 'ca.application_package_id');

        if ($applicationId > 0) {
            $query->where('ca.application_id', $applicationId);
        }

        if ($search !== '') {
            $pattern = '%'.$search.'%';
            $query->where(function ($sub) use ($pattern, $search) {
                $sub->where('c.company_name', 'like', $pattern)
                    ->orWhere('u.email', 'like', $pattern)
                    ->orWhere('u.name', 'like', $pattern)
                    ->orWhere('a.name', 'like', $pattern)
                    ->orWhere('a.slug', 'like', $pattern)
                    ->orWhere('p.name', 'like', $pattern)
                    ->orWhere('ca.status', 'like', $pattern);

                if (ctype_digit($search)) {
                    $sub->orWhere('ca.id', (int) $search);
                }
            });
        }

        $rows = $query->select([
                'ca.id',
                'ca.client_id',
                'ca.application_id',
                'ca.application_package_id',
                'ca.status',
                'ca.purchased_at',
                'ca.expires_at',
                'ca.devices_max',
                'c.company_name',
                'u.id as user_id',
                'u.email',
                'a.name as application_name',
                'a.slug as application_slug',
                'p.name as package_name',
                'p.duration_days as package_duration_days',
            ])
            ->selectRaw(
                '(select count(*) from license_devices ld where ld.user_id = u.id and ld.application_id = ca.application_id) as devices_active'
            )
            ->orderByDesc('ca.purchased_at')
            ->orderByDesc('ca.id')
            ->get()
            ->map(fn ($row) => $this->formatLicenseRow($row, $now));

        return response()->json([
            'licenses' => $rows,
        ]);
    }

    public function changePackage(Request $request, int $clientApplication): JsonResponse
    {
        $packageId = (int) $request->input('package_id', 0);

        if ($packageId <= 0) {
            return response()->json([
                'message' => 'Paket wajib dipilih.',
            ], 422);
        }

        $record = DB::table('client_applications')->where('id', $clientApplication)->first();

        if (! $record) {
            return response()->json([
                'message' => 'Lisensi tidak ditemukan.',
            ], 404);
        }

        $package = ApplicationPackage::query()
            ->where('id', $packageId)
            ->where('application_id', (int) $record->application_id)
            ->first();

        if (! $package) {
            return response()->json([
                'message' => 'Paket tidak valid untuk aplikasi ini.',
            ], 422);
        }

        $previousPackageId = (int) ($record->application_package_id ?? 0);

        if ($previousPackageId === (int) $package->id) {
            return response()->json([
                'message' => 'Paket sudah sama — tidak ada perubahan.',
            ], 422);
        }

        $client = Client::query()->with('user')->find($record->client_id);
        $application = Application::find($record->application_id);

        if (! $client || ! $application || ! $client->user) {
            return response()->json([
                'message' => 'Data client atau aplikasi tidak ditemukan.',
            ], 422);
        }

        $purchasedAt = now();
        $expiresAt = (int) $package->duration_days > 0
            ? $purchasedAt->copy()->addDays((int) $package->duration_days)
            : null;
        $status = $package->is_trial ? 'trial' : 'active';
        $trialConsumed = $package->is_trial || (bool) ($record->trial_consumed ?? false);

        DB::transaction(function () use (
            $clientApplication,
            $package,
            $purchasedAt,
            $expiresAt,
            $status,
            $trialConsumed,
            $client,
            $application,
            $previousPackageId,
        ) {
            DB::table('client_applications')->where('id', $clientApplication)->update([
                'application_package_id' => $package->id,
                'devices_max' => (int) $package->devices_quota,
                'status' => $status,
                'purchased_at' => $purchasedAt,
                'expires_at' => $expiresAt,
                'trial_consumed' => $trialConsumed,
                'updated_at' => now(),
            ]);

            $service = app(TransactionService::class);

            if ($previousPackageId <= 0) {
                $service->recordPurchase($client, $application, $package, 'admin', null, [
                    'client_application_id' => $clientApplication,
                ]);
            } else {
                $currentPackage = ApplicationPackage::find($previousPackageId);
                $service->recordUpgrade(
                    $client->user,
                    $application,
                    $package,
                    $currentPackage,
                    'admin',
                );
            }
        });

        return response()->json([
            'message' => 'Paket lisensi berhasil diperbarui.',
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

        $expiresAtExisting = $record->expires_at ? Carbon::parse($record->expires_at) : null;
        $isExpired = $expiresAtExisting
            ? now()->greaterThan($expiresAtExisting)
            : ((string) $record->status === 'expired');

        if (! $isExpired && (string) $record->status !== 'expired') {
            return response()->json([
                'message' => 'Renew hanya untuk lisensi yang sudah expired. Ganti paket lewat tombol Ganti Paket di halaman Lisensi.',
            ], 422);
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

        $expiresAtExisting = $record->expires_at ? Carbon::parse($record->expires_at) : null;
        $isExpired = $expiresAtExisting
            ? now()->greaterThan($expiresAtExisting)
            : ((string) $record->status === 'expired');

        if ($isExpired || (string) $record->status === 'expired') {
            return response()->json([
                'message' => 'Lisensi sudah tidak aktif. Gunakan Renew jika ingin mengaktifkan kembali.',
            ], 422);
        }

        $client = Client::find($record->client_id);

        DB::transaction(function () use ($record, $client) {
            $updates = [
                'status' => 'expired',
                'updated_at' => now(),
            ];

            if ($record->expires_at && Carbon::parse($record->expires_at)->isFuture()) {
                $updates['expires_at'] = now();
            }

            $package = $record->application_package_id
                ? ApplicationPackage::find($record->application_package_id)
                : null;
            if ($package?->is_trial) {
                $updates['trial_consumed'] = true;
            }

            DB::table('client_applications')->where('id', $record->id)->update($updates);

            if ($client) {
                LicenseDevice::query()
                    ->where('user_id', $client->user_id)
                    ->where('application_id', $record->application_id)
                    ->delete();
            }
        });

        return response()->json([
            'message' => 'Lisensi berhasil di-revoke.',
        ]);
    }

    public function releaseDevices(int $clientApplication): JsonResponse
    {
        $record = DB::table('client_applications')->where('id', $clientApplication)->first();

        if (! $record) {
            return response()->json([
                'message' => 'Lisensi tidak ditemukan.',
            ], 404);
        }

        $client = Client::find($record->client_id);

        if (! $client) {
            return response()->json([
                'message' => 'Client tidak ditemukan.',
            ], 422);
        }

        $deleted = LicenseDevice::query()
            ->where('user_id', $client->user_id)
            ->where('application_id', $record->application_id)
            ->delete();

        return response()->json([
            'message' => $deleted > 0
                ? "{$deleted} perangkat berhasil dilepas."
                : 'Tidak ada perangkat terdaftar untuk lisensi ini.',
            'devices_released' => $deleted,
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
            'devices_active' => (int) ($row->devices_active ?? 0),
            'status' => $displayStatus,
            'is_expired' => $isExpired,
            'purchased_at' => $purchasedAt?->format('d M Y'),
            'expires_at' => $expiresAt ? $expiresAt->format('d M Y') : 'Unlimited',
            'expires_at_raw' => $expiresAt?->toIso8601String(),
        ];
    }
}
