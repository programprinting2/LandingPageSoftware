<?php

namespace App\Services;

use Illuminate\Support\Collection;

class HubCatalogService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildItems(Collection $applications): array
    {
        $realSlugs = $applications->pluck('slug')->filter()->all();

        $items = $applications->values()->map(function ($app, int $index) {
            return [
                'name' => $app->name,
                'description' => $app->description ?: 'Software lisensi dengan landing page, paket harga, trial, dan aktivasi perangkat.',
                'url' => route('landing.application', $app->slug),
                'category' => 'Produksi & Cetak',
                'accent' => 'orange',
                'badge' => $index === 0 ? 'Unggulan' : 'Tersedia',
                'is_dummy' => false,
                'featured' => $index === 0,
            ];
        })->all();

        foreach ($this->dummyProducts() as $dummy) {
            if (in_array($dummy['slug_hint'], $realSlugs, true)) {
                continue;
            }
            $items[] = $dummy;
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function dummyProducts(): array
    {
        return [
            [
                'name' => 'Nesting Pro',
                'description' => 'Optimasi layout gambar di lembar cetak — hemat media DTF & UV dengan algoritma nesting otomatis.',
                'url' => null,
                'category' => 'Efisiensi Produksi',
                'accent' => 'blue',
                'badge' => 'Segera',
                'is_dummy' => true,
                'featured' => false,
                'slug_hint' => 'nesting-pro',
            ],
            [
                'name' => 'Color Match Lab',
                'description' => 'Profil warna, proofing, dan sinkronisasi hasil cetak antar mesin untuk tim produksi.',
                'url' => null,
                'category' => 'Quality Control',
                'accent' => 'violet',
                'badge' => 'Segera',
                'is_dummy' => true,
                'featured' => false,
                'slug_hint' => 'color-match-lab',
            ],
            [
                'name' => 'Print Queue Manager',
                'description' => 'Antrian job cetak multi-operator, status mesin, dan notifikasi SLA untuk jobshop sibuk.',
                'url' => null,
                'category' => 'Operasional',
                'accent' => 'teal',
                'badge' => 'Dalam riset',
                'is_dummy' => true,
                'featured' => false,
                'slug_hint' => 'print-queue-manager',
            ],
        ];
    }
}
