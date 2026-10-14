@extends('layouts.softsale')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hub.css') }}">
@endpush

@section('content')
    @include('hub.components.header')

    <main class="hub-main">
        @php
            $hero = $hub['hero'] ?? [];
            $catalog = $hub['catalog'] ?? [];
            $items = $catalogItems ?? [];
            $featured = collect($items)->first(fn ($i) => !empty($i['featured']) && empty($i['is_dummy']));
            $rest = collect($items)->filter(fn ($i) => empty($i['featured']) || !empty($i['is_dummy']))->values();
        @endphp

        <section class="hub-hero">
            <div class="hub-hero-bg" aria-hidden="true">
                <div class="hub-hero-orb hub-hero-orb--1"></div>
                <div class="hub-hero-orb hub-hero-orb--2"></div>
                <div class="hub-hero-grid"></div>
            </div>
            <div class="container hub-hero-layout">
                <div class="hub-hero-copy">
                    @if(!empty($hero['badge']))
                        <div class="hub-hero-badge">{{ $hero['badge'] }}</div>
                    @endif
                    <h1>{{ $hero['title'] ?? 'Software untuk bisnis Anda' }}</h1>
                    @if(!empty($hero['subtitle']))
                        <p class="hub-hero-sub">{{ $hero['subtitle'] }}</p>
                    @endif
                    <div class="hub-hero-actions">
                        <a href="#produk" class="hub-btn hub-btn--primary">Lihat produk</a>
                        <button type="button" class="hub-btn hub-btn--secondary" onclick="openModal('register')">Mulai trial</button>
                    </div>
                    <div class="hub-trust">
                        <span>Lisensi cloud</span>
                        <span>Trial per app</span>
                        <span>Pembayaran terintegrasi</span>
                    </div>
                </div>
                <div class="hub-hero-visual" aria-hidden="true">
                    <div class="hub-mock hub-mock--back">
                        <div class="hub-mock-bar"></div>
                        <div class="hub-mock-line"></div>
                        <div class="hub-mock-line short"></div>
                    </div>
                    <div class="hub-mock hub-mock--front">
                        <div class="hub-mock-label">Dashboard lisensi</div>
                        <div class="hub-mock-stat-row">
                            <div class="hub-mock-pill active"></div>
                            <div class="hub-mock-pill"></div>
                            <div class="hub-mock-pill"></div>
                        </div>
                        <div class="hub-mock-chart"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="hub-value">
            <div class="container hub-value-grid">
                <div class="hub-value-card">
                    <div class="hub-value-icon">⚡</div>
                    <h3>Go-live cepat</h3>
                    <p>Landing, harga, dan trial per produk tanpa rebuild portal dari nol.</p>
                </div>
                <div class="hub-value-card">
                    <div class="hub-value-icon">🔐</div>
                    <h3>Lisensi terkontrol</h3>
                    <p>Aktivasi perangkat, masa berlaku, dan renewals dalam satu sistem.</p>
                </div>
                <div class="hub-value-card">
                    <div class="hub-value-icon">📈</div>
                    <h3>Siap multi-produk</h3>
                    <p>Tambah software baru — katalog hub dan landing masing-masing otomatis mengikuti.</p>
                </div>
            </div>
        </section>

        <section class="hub-catalog" id="produk">
            <div class="container">
                <div class="hub-catalog-head">
                    <h2>{{ $catalog['title'] ?? 'Produk kami' }}</h2>
                    @if(!empty($catalog['subtitle']))
                        <p>{{ $catalog['subtitle'] }}</p>
                    @endif
                </div>

                @if($featured)
                    <div class="hub-featured-wrap">
                        @include('hub.components.product-card', ['item' => $featured])
                    </div>
                @endif

                <div class="hub-grid">
                    @forelse($rest as $item)
                        @include('hub.components.product-card', ['item' => $item])
                    @empty
                        @if(!$featured)
                            <p class="hub-empty">{{ $catalog['empty_text'] ?? 'Belum ada aplikasi aktif.' }}</p>
                        @endif
                    @endforelse
                </div>
            </div>
        </section>

        <section class="hub-cta">
            <div class="container hub-cta-inner">
                <div>
                    <h2>Siap jual software berikutnya?</h2>
                    <p>Tambahkan aplikasi di Master Aplikasi — kartu produk di halaman ini mengikuti otomatis.</p>
                </div>
                <button type="button" class="hub-btn hub-btn--primary hub-btn--lg" onclick="openModal('register')">Buat akun client</button>
            </div>
        </section>
    </main>

    @include('hub.components.footer')
    @include('shared.components.auth-modal')
    @include('shared.components.app-dialog')
@endsection
