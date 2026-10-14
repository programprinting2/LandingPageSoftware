<?php

namespace App\Services;

use App\Models\Application;
use App\Models\LandingContent;

class LandingContentService
{
    public const SECTIONS = [
        'site',
        'hero',
        'video',
        'workflow',
        'features',
        'pricing',
        'faq',
        'footer',
    ];

    public function __construct(private LandingPricingService $landingPricing) {}

    public function defaults(): array
    {
        return [
            'site' => [
                'name' => (string) config('softsale.name'),
                'tagline' => (string) config('softsale.tagline'),
            ],
            'hero' => [
                'title' => 'Solusi',
                'title_highlight' => 'SATSET',
                'title_suffix' => 'cetak DTF! Proses PNG siap cetak hanya dengan',
                'title_highlight_2' => '1x klik',
                'subtitle' => 'Tinggalkan proses bikin spot color & white mask manual.',
                'cta_primary' => 'Coba Gratis',
                'cta_secondary' => 'Lihat Video',
            ],
            'video' => [
                'url' => '',
            ],
            'workflow' => [
                'title' => 'Alur Kerja Mudah dan Simpel',
                'steps' => [
                    ['title' => 'Scan & Input File', 'desc' => 'Masukkan file PNG hasil layout atau nesting Anda.'],
                    ['title' => 'Atur Parameter', 'desc' => 'Atur contract sesuai kebutuhan cetak dan mesin (opsional).'],
                    ['title' => 'Proses', 'desc' => 'Klik proses dan tunggu file PDF selesai di export.'],
                    ['title' => 'Cetak', 'desc' => 'Drag and drop file PDF siap cetak ke software mesin cetak Anda.'],
                ],
            ],
            'features' => [
                'title' => 'Dirancang untuk Produktivitas Cetak',
                'items' => config('softsale.features', []),
            ],
            'pricing' => [
                'badge' => 'Harga',
                'title' => 'Pilih Paket yang Tepat',
                'subtitle' => 'Mulai gratis, upgrade kapan saja sesuai kebutuhan produksi Anda.',
                'items' => config('softsale.plans', []),
                'package_overrides' => [],
            ],
            'faq' => [
                'title' => 'Pertanyaan Umum',
                'subtitle' => 'Jawaban untuk pertanyaan yang sering ditanyakan.',
                'items' => config('softsale.faq', []),
            ],
            'footer' => [
                'description' => 'Software otomatis spot color & white mask untuk printing DTF. Hemat waktu, tingkatkan produktivitas.',
                'copyright' => '© '.date('Y').' Quick Spot Color. All rights reserved.',
            ],
        ];
    }

    public function all(?int $applicationId = null): array
    {
        $applicationId = $this->resolveApplicationId($applicationId);
        $defaults = $this->defaults();
        $stored = LandingContent::query()
            ->where('application_id', $applicationId)
            ->pluck('payload', 'key');

        $merged = [];
        foreach ($defaults as $key => $default) {
            $payload = $stored->get($key);
            $merged[$key] = is_array($payload)
                ? array_replace_recursive($default, $payload)
                : $default;
        }

        return $merged;
    }

    public function section(string $key, ?int $applicationId = null): array
    {
        $defaults = $this->defaults();

        if (! isset($defaults[$key])) {
            return [];
        }

        $applicationId = $this->resolveApplicationId($applicationId);

        $record = LandingContent::query()
            ->where('application_id', $applicationId)
            ->where('key', $key)
            ->first();
        $payload = $record?->payload;

        return is_array($payload)
            ? array_replace_recursive($defaults[$key], $payload)
            : $defaults[$key];
    }

    public function updateSection(string $key, array $payload, ?int $applicationId = null): array
    {
        if (! in_array($key, self::SECTIONS, true)) {
            abort(404);
        }

        $applicationId = $this->resolveApplicationId($applicationId);

        LandingContent::query()->updateOrCreate(
            [
                'application_id' => $applicationId,
                'key' => $key,
            ],
            ['payload' => $payload],
        );

        return $this->section($key, $applicationId);
    }

    /**
     * @return array<string, mixed>
     */
    public function landingDataPayloadForApplication(Application $application): array
    {
        $cms = $this->all($application->id);
        $application->loadMissing('packages');

        return [
            'site' => $cms['site'],
            'hero' => $cms['hero'],
            'plans' => $this->landingPricing->buildPlans($application, $cms['pricing'] ?? []),
            'pricingSection' => [
                'badge' => $cms['pricing']['badge'] ?? '',
                'title' => $cms['pricing']['title'] ?? '',
                'subtitle' => $cms['pricing']['subtitle'] ?? '',
            ],
            'footer' => $cms['footer'],
            'application' => [
                'id' => $application->id,
                'name' => $application->name,
                'slug' => $application->slug,
            ],
        ];
    }

    public function landingDataPayload(?int $applicationId = null): array
    {
        $applicationId = $this->resolveApplicationId($applicationId);
        $application = Application::query()->with('packages')->findOrFail($applicationId);

        return $this->landingDataPayloadForApplication($application);
    }

    public function resolveApplicationId(?int $applicationId): int
    {
        if ($applicationId) {
            return $applicationId;
        }

        $slug = (string) config('softsale.default_application_slug', 'spot-color');
        $id = Application::query()->where('slug', $slug)->value('id');

        if ($id) {
            return (int) $id;
        }

        $fallback = Application::query()->orderBy('sort_order')->orderBy('id')->value('id');

        if (! $fallback) {
            abort(503, 'Belum ada aplikasi aktif.');
        }

        return (int) $fallback;
    }
}
