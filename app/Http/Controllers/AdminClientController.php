<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminClientRequest;
use App\Http\Requests\UpdateAdminClientRequest;
use App\Models\Application;
use App\Models\ApplicationPackage;
use App\Models\Client;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminClientController extends Controller
{
    public function index(): JsonResponse
    {
        $clients = Client::query()
            ->with(['user:id,name,email,email_verified_at'])
            ->latest()
            ->get()
            ->map(fn (Client $client) => $this->formatClient($client));

        return response()->json([
            'clients' => $clients,
        ]);
    }

    public function store(StoreAdminClientRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $client = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'client',
                'email_verified_at' => $this->resolveEmailVerifiedAt($validated, null),
            ]);

            $client = Client::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'whatsapp' => $validated['whatsapp'],
                'status' => $validated['status'],
                'devices_max' => config('spotcolor.devices_max', 3),
            ]);

            $this->syncClientApplications($client, $this->resolveApplicationAssignments($validated));
            $this->recordClientApplicationTransactions($client, $this->resolveApplicationAssignments($validated), 'purchase');

            return $client;
        });

        return response()->json([
            'message' => 'Client berhasil ditambahkan.',
            'client' => $this->formatClient($client->fresh()),
        ], 201);
    }

    public function update(UpdateAdminClientRequest $request, Client $client): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($client, $validated) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            if (array_key_exists('email_verified', $validated)) {
                $userData['email_verified_at'] = $this->resolveEmailVerifiedAt($validated, $client->user);
            }

            $client->user->update($userData);

            $client->update([
                'company_name' => $validated['company_name'],
                'whatsapp' => $validated['whatsapp'],
                'status' => $validated['status'],
            ]);

            if (array_key_exists('applications', $validated) || array_key_exists('application_ids', $validated)) {
                $before = $client->applications()
                    ->get()
                    ->keyBy('id')
                    ->map(fn ($application) => (int) $application->pivot->application_package_id);

                $assignments = $this->resolveApplicationAssignments($validated);
                $this->syncClientApplications($client, $assignments);

                $client->load('applications');
                $this->recordClientApplicationChanges($client, $before, $assignments);
            }
        });

        return response()->json([
            'message' => 'Client berhasil diperbarui.',
            'client' => $this->formatClient($client->fresh()),
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        DB::transaction(function () use ($client) {
            $user = $client->user;
            $client->applications()->detach();
            $client->delete();
            $user?->delete();
        });

        return response()->json([
            'message' => 'Client berhasil dihapus.',
        ]);
    }

    private function resolveApplicationAssignments(array $validated): array
    {
        if (! empty($validated['applications']) && is_array($validated['applications'])) {
            return $validated['applications'];
        }

        return collect($validated['application_ids'] ?? [])
            ->map(fn ($applicationId) => ['application_id' => (int) $applicationId])
            ->all();
    }

    private function syncClientApplications(Client $client, array $assignments): void
    {
        $sync = [];

        foreach ($assignments as $assignment) {
            $applicationId = (int) ($assignment['application_id'] ?? $assignment['id'] ?? 0);
            $packageId = (int) ($assignment['package_id'] ?? 0);

            if ($applicationId <= 0) {
                continue;
            }

            $existing = $client->applications()
                ->where('applications.id', $applicationId)
                ->first();

            $package = $packageId > 0
                ? ApplicationPackage::query()
                    ->where('id', $packageId)
                    ->where('application_id', $applicationId)
                    ->first()
                : null;

            if (! $package) {
                throw ValidationException::withMessages([
                    'applications' => "Paket untuk aplikasi ID {$applicationId} wajib dipilih.",
                ]);
            }

            $purchasedAt = $this->parsePivotDate($existing?->pivot?->purchased_at) ?? now();
            $expiresAt = (int) $package->duration_days > 0
                ? $purchasedAt->copy()->addDays((int) $package->duration_days)
                : null;

            $sync[$applicationId] = [
                'application_package_id' => $package->id,
                'devices_max' => (int) $package->devices_quota,
                'status' => 'active',
                'purchased_at' => $purchasedAt,
                'expires_at' => $expiresAt,
            ];
        }

        $client->applications()->sync($sync);
    }

    private function formatClient(Client $client): array
    {
        $client->load([
            'user:id,name,email,email_verified_at',
            'applications' => fn ($query) => $query
                ->select('applications.id', 'applications.name', 'applications.slug')
                ->withPivot(['application_package_id', 'devices_max', 'status', 'purchased_at', 'expires_at']),
        ]);

        $packages = $this->packagesForClientApplications($client->applications);

        return [
            'id' => $client->id,
            'name' => $client->user?->name,
            'company_name' => $client->company_name,
            'email' => $client->user?->email,
            'email_verified' => $client->user?->email_verified_at !== null,
            'email_verified_at' => $client->user?->email_verified_at?->format('d M Y H:i'),
            'whatsapp' => $client->whatsapp,
            'status' => $client->status,
            'application_ids' => $client->applications->pluck('id')->values()->all(),
            'applications' => $client->applications
                ->map(function ($application) use ($packages) {
                    $package = $packages->get((int) $application->pivot->application_package_id);

                    return [
                        'id' => $application->id,
                        'name' => $application->name,
                        'slug' => $application->slug,
                        'package_id' => $package?->id,
                        'package' => $package ? $this->formatPackage($package) : null,
                        'devices_max' => (int) ($application->pivot->devices_max ?: $package?->devices_quota),
                        'purchase_status' => $application->pivot->status,
                        'purchased_at' => $this->formatPivotDate($application->pivot->purchased_at),
                        'expires_at' => $this->formatPivotDate($application->pivot->expires_at),
                    ];
                })
                ->values()
                ->all(),
            'created_at' => $client->created_at?->format('d M Y'),
        ];
    }

    private function packagesForClientApplications(Collection $applications): Collection
    {
        $packageIds = $applications
            ->pluck('pivot.application_package_id')
            ->filter()
            ->unique()
            ->values();

        if ($packageIds->isEmpty()) {
            return collect();
        }

        return ApplicationPackage::query()
            ->whereIn('id', $packageIds)
            ->get()
            ->keyBy('id');
    }

    private function formatPackage(ApplicationPackage $package): array
    {
        return [
            'id' => $package->id,
            'name' => $package->name,
            'devices_quota' => (int) $package->devices_quota,
            'duration_days' => (int) $package->duration_days,
            'price' => (float) $package->price,
        ];
    }

    private function resolveEmailVerifiedAt(array $validated, ?User $user): ?Carbon
    {
        if (! array_key_exists('email_verified', $validated)) {
            return $user?->email_verified_at;
        }

        if ($this->isTruthy($validated['email_verified'])) {
            return $user?->email_verified_at ?? now();
        }

        return null;
    }

    private function isTruthy(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    private function parsePivotDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function formatPivotDate(mixed $value): ?string
    {
        return $this->parsePivotDate($value)?->format('d M Y');
    }

    private function recordClientApplicationTransactions(Client $client, array $assignments, string $type): void
    {
        $service = app(TransactionService::class);

        foreach ($assignments as $assignment) {
            $applicationId = (int) ($assignment['application_id'] ?? $assignment['id'] ?? 0);
            $packageId = (int) ($assignment['package_id'] ?? 0);

            if ($applicationId <= 0 || $packageId <= 0) {
                continue;
            }

            $application = Application::find($applicationId);
            $package = ApplicationPackage::query()
                ->where('id', $packageId)
                ->where('application_id', $applicationId)
                ->first();

            if (! $application || ! $package) {
                continue;
            }

            if ($type === 'purchase') {
                $service->recordPurchase($client, $application, $package, 'admin');
            }
        }
    }

    private function recordClientApplicationChanges(Client $client, Collection $beforePackages, array $assignments): void
    {
        $service = app(TransactionService::class);

        foreach ($assignments as $assignment) {
            $applicationId = (int) ($assignment['application_id'] ?? $assignment['id'] ?? 0);
            $packageId = (int) ($assignment['package_id'] ?? 0);

            if ($applicationId <= 0 || $packageId <= 0) {
                continue;
            }

            $previousPackageId = (int) ($beforePackages->get($applicationId) ?? 0);

            if ($previousPackageId === $packageId) {
                continue;
            }

            $application = Application::find($applicationId);
            $package = ApplicationPackage::query()
                ->where('id', $packageId)
                ->where('application_id', $applicationId)
                ->first();

            if (! $application || ! $package) {
                continue;
            }

            if ($previousPackageId <= 0) {
                $service->recordPurchase($client, $application, $package, 'admin');
                continue;
            }

            $currentPackage = ApplicationPackage::find($previousPackageId);
            $service->recordUpgrade(
                $client->user,
                $application,
                $package,
                $currentPackage,
                'admin',
            );
        }
    }
}
