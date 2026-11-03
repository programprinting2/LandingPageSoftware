<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminApplicationRequest;
use App\Http\Requests\UpdateAdminApplicationRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminApplicationController extends Controller
{
    public function index(): JsonResponse
    {
        $applications = Application::query()
            ->with(['packages'])
            ->withCount(['clients', 'licenseDevices', 'packages'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Application $application) => $this->formatApplication($application));

        return response()->json([
            'applications' => $applications,
        ]);
    }

    public function store(StoreAdminApplicationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $slug = $this->resolveSlug($validated['slug'] ?? null, $validated['name']);

        $application = DB::transaction(function () use ($validated, $slug) {
            $application = Application::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'logo_url' => $this->normalizeLogoUrl($validated['logo_url'] ?? null),
                'default_devices_max' => (int) $validated['default_devices_max'],
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'notification_enabled' => (bool) ($validated['notification_enabled'] ?? false),
                'notification_image_url' => $this->normalizeLogoUrl($validated['notification_image_url'] ?? null),
                'notification_package_ids' => $this->normalizeNotificationPackageIds($validated['notification_package_ids'] ?? []),
            ]);

            $this->syncPackages($application, $validated['packages'] ?? []);

            return $application;
        });

        $application->load('packages');

        return response()->json([
            'message' => 'Aplikasi berhasil ditambahkan.',
            'application' => $this->formatApplication($application),
        ], 201);
    }

    public function update(UpdateAdminApplicationRequest $request, Application $application): JsonResponse
    {
        $validated = $request->validated();
        $slug = $this->resolveSlug($validated['slug'] ?? $application->slug, $validated['name'], $application->id);

        DB::transaction(function () use ($application, $validated, $slug) {
            $application->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'logo_url' => $this->normalizeLogoUrl($validated['logo_url'] ?? null),
                'default_devices_max' => (int) $validated['default_devices_max'],
                'is_active' => (bool) ($validated['is_active'] ?? $application->is_active),
                'sort_order' => (int) ($validated['sort_order'] ?? $application->sort_order),
                'notification_enabled' => (bool) ($validated['notification_enabled'] ?? $application->notification_enabled),
                'notification_image_url' => array_key_exists('notification_image_url', $validated)
                    ? $this->normalizeLogoUrl($validated['notification_image_url'])
                    : $application->notification_image_url,
                'notification_package_ids' => array_key_exists('notification_package_ids', $validated)
                    ? $this->normalizeNotificationPackageIds($validated['notification_package_ids'] ?? [])
                    : ($application->notification_package_ids ?? []),
            ]);

            if (array_key_exists('packages', $validated)) {
                $this->syncPackages($application, $validated['packages'] ?? []);
            }
        });

        $application->refresh()->load('packages');

        return response()->json([
            'message' => 'Aplikasi berhasil diperbarui.',
            'application' => $this->formatApplication($application),
        ]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,gif,svg', 'max:5120'],
        ]);

        $directory = public_path('images/applications');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = $request->file('image');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $filename = Str::uuid().'.'.$extension;
        $file->move($directory, $filename);

        return response()->json([
            'url' => '/images/applications/'.$filename,
        ]);
    }

    public function destroy(Application $application): JsonResponse
    {
        if ($application->licenseDevices()->exists()) {
            return response()->json([
                'message' => 'Aplikasi tidak dapat dihapus karena masih memiliki aktivasi lisensi.',
            ], 422);
        }

        if ($application->clients()->exists()) {
            return response()->json([
                'message' => 'Aplikasi tidak dapat dihapus karena masih ditetapkan ke client.',
            ], 422);
        }

        $application->delete();

        return response()->json([
            'message' => 'Aplikasi berhasil dihapus.',
        ]);
    }

    private function syncPackages(Application $application, array $packages): void
    {
        $existing = $application->packages()->get()->keyBy('id');
        $retainedIds = [];

        foreach (array_values($packages) as $index => $package) {
            $name = trim((string) ($package['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $attributes = [
                'name' => $name,
                'is_trial' => (bool) ($package['is_trial'] ?? false)
                    || strtolower($name) === 'trial'
                    || str_contains(strtolower($name), 'trial'),
                'devices_quota' => (int) ($package['devices_quota'] ?? 1),
                'device_slot_price' => (float) ($package['device_slot_price'] ?? 0),
                'max_extra_device_slots' => (int) ($package['max_extra_device_slots'] ?? 0),
                'duration_days' => (int) ($package['duration_days'] ?? 0),
                'price' => (float) ($package['price'] ?? 0),
                'discount_percent' => (float) ($package['discount_percent'] ?? 0),
                'affiliate_commission_fixed' => (float) ($package['affiliate_commission_fixed'] ?? 0),
                'affiliate_commission_percent' => (float) ($package['affiliate_commission_percent'] ?? 0),
                'sort_order' => (int) ($package['sort_order'] ?? $index),
                'parallel_max' => $this->normalizeParallelMax($package['parallel_max'] ?? null),
                'feature_uv' => (bool) ($package['feature_uv'] ?? true),
            ];

            $packageId = (int) ($package['id'] ?? 0);
            if ($packageId > 0 && $existing->has($packageId)) {
                $existing->get($packageId)->update($attributes);
                $retainedIds[] = $packageId;
            } else {
                $created = $application->packages()->create($attributes);
                $retainedIds[] = $created->id;
            }
        }

        if ($retainedIds !== []) {
            $application->packages()->whereNotIn('id', $retainedIds)->delete();
        } else {
            $application->packages()->delete();
        }
    }

    private function normalizeParallelMax(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $max = (int) $value;

        return $max >= 1 && $max <= 99 ? $max : null;
    }

    private function normalizeLogoUrl(mixed $value): ?string
    {
        $url = trim((string) ($value ?? ''));

        return $url !== '' ? $url : null;
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function normalizeNotificationPackageIds(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($slug ?: $name));

        if ($base === '') {
            $base = 'aplikasi';
        }

        $candidate = $base;
        $suffix = 2;

        while (
            Application::where('slug', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function formatApplication(Application $application): array
    {
        $packages = $application->relationLoaded('packages')
            ? $application->packages
            : $application->packages()->get();

        return [
            'id' => $application->id,
            'name' => $application->name,
            'slug' => $application->slug,
            'description' => $application->description,
            'logo_url' => $application->logo_url,
            'default_devices_max' => $application->default_devices_max,
            'is_active' => $application->is_active,
            'sort_order' => $application->sort_order,
            'notification_enabled' => (bool) $application->notification_enabled,
            'notification_image_url' => $application->notification_image_url,
            'notification_package_ids' => array_values(array_map('intval', $application->notification_package_ids ?? [])),
            'clients_count' => $application->clients_count ?? $application->clients()->count(),
            'devices_count' => $application->license_devices_count ?? $application->licenseDevices()->count(),
            'packages_count' => $application->packages_count ?? $packages->count(),
            'packages' => $packages
                ->map(fn ($package) => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'devices_quota' => $package->devices_quota,
                    'device_slot_price' => (float) $package->device_slot_price,
                    'max_extra_device_slots' => (int) $package->max_extra_device_slots,
                    'duration_days' => $package->duration_days,
                    'price' => (float) $package->price,
                    'discount_percent' => (float) $package->discount_percent,
                    'affiliate_commission_fixed' => (float) $package->affiliate_commission_fixed,
                    'affiliate_commission_percent' => (float) $package->affiliate_commission_percent,
                    'sort_order' => $package->sort_order,
                    'parallel_max' => $package->parallel_max !== null ? (int) $package->parallel_max : null,
                    'feature_uv' => (bool) $package->feature_uv,
                ])
                ->values()
                ->all(),
            'created_at' => $application->created_at?->format('d M Y'),
        ];
    }
}
