<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminApplicationRequest;
use App\Http\Requests\UpdateAdminApplicationRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
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
                'default_devices_max' => (int) $validated['default_devices_max'],
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
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
                'default_devices_max' => (int) $validated['default_devices_max'],
                'is_active' => (bool) ($validated['is_active'] ?? $application->is_active),
                'sort_order' => (int) ($validated['sort_order'] ?? $application->sort_order),
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
        $application->packages()->delete();

        foreach (array_values($packages) as $index => $package) {
            $name = trim((string) ($package['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $application->packages()->create([
                'name' => $name,
                'devices_quota' => (int) ($package['devices_quota'] ?? 1),
                'duration_days' => (int) ($package['duration_days'] ?? 0),
                'price' => (float) ($package['price'] ?? 0),
                'sort_order' => (int) ($package['sort_order'] ?? $index),
            ]);
        }
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
            'default_devices_max' => $application->default_devices_max,
            'is_active' => $application->is_active,
            'sort_order' => $application->sort_order,
            'clients_count' => $application->clients_count ?? $application->clients()->count(),
            'devices_count' => $application->license_devices_count ?? $application->licenseDevices()->count(),
            'packages_count' => $application->packages_count ?? $packages->count(),
            'packages' => $packages
                ->map(fn ($package) => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'devices_quota' => $package->devices_quota,
                    'duration_days' => $package->duration_days,
                    'price' => (float) $package->price,
                    'sort_order' => $package->sort_order,
                ])
                ->values()
                ->all(),
            'created_at' => $application->created_at?->format('d M Y'),
        ];
    }
}
