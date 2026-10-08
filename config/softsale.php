<?php

return [
    'name' => env('SOFTSALE_NAME', 'Quick Spot Color'),
    'tagline' => 'Solusi SATSET Cetak DTF',

    'features' => [
        ['title' => 'Drag and Drop', 'desc' => 'Drag and drop dari file explorer hingga ke software print manager Anda.'],
        ['title' => 'PNG Folder Scanner', 'desc' => 'Menambahkan seluruh file PNG dari directory hingga sub folder ke dalam daftar proses.'],
        ['title' => 'Contract Underbase & Warna Putih', 'desc' => 'Atur contract underbase dan warna putih sesuai kebutuhan cetak dan mesin Anda.'],
    ],

    'faq' => [
        ['question' => 'Apa itu Quick Spot Color?', 'answer' => 'Quick Spot Color adalah software otomatis untuk membuat spot color dan white mask pada file PNG, khusus untuk kebutuhan printing DTF.'],
        ['question' => 'Apakah ada versi trial?', 'answer' => 'Ya, Anda bisa mencoba gratis dengan mendaftar akun. Trial memberikan akses penuh ke fitur utama selama periode percobaan.'],
        ['question' => 'Format file apa yang didukung?', 'answer' => 'Software mendukung file PNG dengan transparansi. Anda bisa memproses file individual atau seluruh folder sekaligus.'],
        ['question' => 'Apakah perlu instalasi khusus?', 'answer' => 'Software berjalan di Windows. Setelah registrasi, unduh installer dari dashboard klien dan aktifkan dengan lisensi Anda.'],
        ['question' => 'Bagaimana cara pembayaran?', 'answer' => 'Pembayaran dilakukan melalui transfer bank atau payment gateway. Pilih paket di halaman harga dan ikuti instruksi checkout.'],
    ],

    'plans' => [
        ['name' => 'Trial', 'price' => 'Gratis', 'period' => '/14 hari', 'features' => ['1 Perangkat', 'Semua Fitur', 'Folder Scanner', 'Email Support'], 'popular' => false],
        ['name' => 'Pro', 'price' => 'Rp 1.500.000', 'period' => '/tahun', 'features' => ['2 Perangkat', 'Batch Processing', 'White Mask Otomatis', 'Priority Support', 'Update Gratis'], 'popular' => true],
        ['name' => 'Business', 'price' => 'Rp 3.500.000', 'period' => '/tahun', 'features' => ['5 Perangkat', 'Unlimited Batch', 'Custom Preset', 'WhatsApp Support', 'Training Online'], 'popular' => false],
    ],

    'referral_base_url' => env('APP_URL', 'http://localhost:8000'),

    'default_application_slug' => env('SOFTSALE_DEFAULT_APP_SLUG', 'spot-color'),
    'default_trial_package_name' => env('SOFTSALE_DEFAULT_TRIAL_PACKAGE', 'trial'),
];
