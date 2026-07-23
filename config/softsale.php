<?php

return [
    'name' => env('SOFTSALE_NAME', 'SoftSale'),
    'tagline' => 'Platform Manajemen Bisnis',

    'features' => [
        ['icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>', 'title' => 'Keamanan Tingkat Tinggi', 'desc' => 'Enkripsi end-to-end, hardware-bound license, dan proteksi source code berlapis untuk ketenangan bisnis Anda.', 'color' => 'var(--accent-1)'],
        ['icon' => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>', 'title' => 'Performa Cepat', 'desc' => 'Arsitektur modern yang dioptimasi untuk respon instan, bahkan dengan ribuan transaksi harian.', 'color' => 'var(--accent-2)'],
        ['icon' => '<path d="M18 20V10M12 20V4M6 20v-6"/>', 'title' => 'Analitik Real-time', 'desc' => 'Dashboard monitoring bisnis dengan grafik interaktif dan insight yang mudah dipahami kapan saja.', 'color' => 'var(--green)'],
        ['icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>', 'title' => 'Modular & Scalable', 'desc' => 'Pilih modul sesuai kebutuhan dan tambahkan kapanpun bisnis Anda bertumbuh tanpa migrasi.', 'color' => 'var(--amber)'],
        ['icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>', 'title' => 'Multi-User & Role', 'desc' => 'Atur hak akses per user dengan sistem role management yang fleksibel dan granular.', 'color' => 'var(--rose)'],
        ['icon' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>', 'title' => 'Integrasi Luas', 'desc' => 'Terhubung dengan WhatsApp, payment gateway, marketplace, dan API pihak ketiga dengan mudah.', 'color' => '#A78BFA'],
    ],

    'plans' => [
        ['name' => 'Starter', 'price' => 'Rp 2.000.000', 'period' => '/tahun', 'features' => ['1 Lokasi', '5 User', 'Modul Dasar', 'Email Support', '14 Hari Trial'], 'popular' => false],
        ['name' => 'Professional', 'price' => 'Rp 4.000.000', 'period' => '/tahun', 'features' => ['5 Lokasi', '25 User', 'Semua Modul', 'WhatsApp Notifikasi', 'Priority Support', 'API Access'], 'popular' => true],
        ['name' => 'Enterprise', 'price' => 'Rp 8.000.000', 'period' => '/tahun', 'features' => ['Unlimited Lokasi', 'Unlimited User', 'Custom Module', 'Dedicated Server', '24/7 Phone Support', 'On-site Training', 'SLA 99.9%'], 'popular' => false],
    ],

    'testimonials' => [
        ['name' => 'Budi Santoso', 'role' => 'Owner, PT Maju Jaya', 'text' => 'Sejak pakai SoftSale, operasional bisnis kami jadi jauh lebih efisien. ROI tercapai dalam 3 bulan pertama!', 'avatar' => 'BS'],
        ['name' => 'Rina Kusuma', 'role' => 'Manager, CV Tekno Solusi', 'text' => 'Sistem yang sangat reliable. Tim support-nya responsif dan update fiturnya rutin setiap bulan.', 'avatar' => 'RK'],
        ['name' => 'Ahmad Fauzi', 'role' => 'Director, UD Sinar Abadi', 'text' => 'Fitur analitik real-time-nya luar biasa. Sekarang setiap keputusan bisnis kami jauh lebih data-driven.', 'avatar' => 'AF'],
    ],

    'about_values' => [
        ['icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>', 'title' => 'Terpercaya', 'desc' => 'Digunakan 500+ bisnis aktif di seluruh Indonesia', 'color' => 'var(--amber)'],
        ['icon' => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>', 'title' => 'Inovatif', 'desc' => 'Update fitur rutin berdasarkan feedback pengguna nyata', 'color' => 'var(--amber)'],
        ['icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>', 'title' => 'Supportif', 'desc' => 'Tim support siap membantu via WhatsApp & telepon kapan saja', 'color' => 'var(--amber)'],
        ['icon' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>', 'title' => 'Bersertifikasi', 'desc' => 'Standar keamanan ISO 27001 dan enkripsi data berlapis', 'color' => 'var(--amber)'],
    ],

    'contacts' => [
        ['icon' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6"/>', 'title' => 'Email', 'detail' => 'hello@softsale.id', 'sub' => 'Respon dalam 24 jam kerja', 'color' => 'var(--rose)'],
        ['icon' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>', 'title' => 'Telepon', 'detail' => '+62 21 1234 5678', 'sub' => 'Sen – Jum, 08:00 – 17:00 WIB', 'color' => 'var(--rose)'],
        ['icon' => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>', 'title' => 'WhatsApp', 'detail' => '+62 812 3456 7890', 'sub' => 'Fast response, chat langsung', 'color' => 'var(--rose)'],
    ],

    'referral_base_url' => env('APP_URL', 'http://localhost:8000'),

    'default_application_slug' => env('SOFTSALE_DEFAULT_APP_SLUG', 'spot-color'),
    'default_trial_package_name' => env('SOFTSALE_DEFAULT_TRIAL_PACKAGE', 'trial'),
];
