<section class="hero" id="hero">
  <div class="hero-bg-decor" aria-hidden="true">
    <div class="hero-memphis-blob hero-memphis-blob--navy"></div>

    <svg class="hero-memphis-svg hero-memphis-svg--wave" viewBox="0 0 600 400" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M-20 280 C120 180, 260 340, 420 220 S620 80, 700 160" stroke="#FF5500" stroke-width="4" stroke-linecap="round" opacity="0.55"/>
      <path d="M40 320 C180 240, 300 380, 460 280 S640 160, 720 220" stroke="#0F172A" stroke-width="2.5" stroke-linecap="round" opacity="0.2"/>
    </svg>

    <svg class="hero-memphis-svg hero-memphis-svg--squiggle" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M20 100 C50 20, 90 180, 120 60 S170 140, 180 40" stroke="#FF5500" stroke-width="3.5" stroke-linecap="round" opacity="0.7"/>
      <circle cx="170" cy="40" r="6" fill="#0F172A" opacity="0.35"/>
    </svg>

    <svg class="hero-memphis-svg hero-memphis-svg--zigzag" viewBox="0 0 120 60" fill="none" xmlns="http://www.w3.org/2000/svg">
      <polyline points="0,30 20,10 40,30 60,10 80,30 100,10 120,30" stroke="#0F172A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" opacity="0.25"/>
    </svg>

    <svg class="hero-memphis-svg hero-memphis-svg--dots" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="20" cy="20" r="4" fill="#FF5500" opacity="0.5"/>
      <circle cx="50" cy="20" r="4" fill="#0F172A" opacity="0.3"/>
      <circle cx="80" cy="20" r="4" fill="#FF5500" opacity="0.4"/>
      <circle cx="20" cy="50" r="4" fill="#0F172A" opacity="0.25"/>
      <circle cx="50" cy="50" r="4" fill="#FF5500" opacity="0.55"/>
      <circle cx="80" cy="50" r="4" fill="#0F172A" opacity="0.3"/>
      <circle cx="35" cy="80" r="4" fill="#FF5500" opacity="0.45"/>
      <circle cx="65" cy="80" r="4" fill="#0F172A" opacity="0.25"/>
    </svg>

    <div class="hero-memphis-shape hero-memphis-ring hero-memphis-ring--1"></div>
    <div class="hero-memphis-shape hero-memphis-ring hero-memphis-ring--2"></div>
    <div class="hero-memphis-shape hero-memphis-disc hero-memphis-disc--1"></div>
    <div class="hero-memphis-shape hero-memphis-disc hero-memphis-disc--2"></div>
    <div class="hero-memphis-shape hero-memphis-tri"></div>
    <div class="hero-memphis-shape hero-memphis-cross"></div>
    <div class="hero-memphis-shape hero-memphis-pill"></div>
    <div class="hero-memphis-shape hero-memphis-arc"></div>
  </div>
  <div class="container hero-inner">
    <h1 class="hero-headline reveal">
      {{ $cms['hero']['title'] ?? 'Solusi' }}
      <span class="text-accent">{{ $cms['hero']['title_highlight'] ?? 'SATSET' }}</span>
      {{ $cms['hero']['title_suffix'] ?? 'cetak DTF! Proses PNG siap cetak hanya dengan' }}
      <span class="text-accent">{{ $cms['hero']['title_highlight_2'] ?? '1x klik' }}</span>.
    </h1>
    <div class="hero-visual reveal reveal-delay-1">
      <img src="{{ asset('images/quick spot color hero.png') }}" alt="Quick Spot Color — Software DTF Printing">
    </div>
    <p class="hero-sub reveal reveal-delay-2">
      {{ $cms['hero']['subtitle'] ?? 'Tinggalkan proses bikin spot color & white mask manual.' }}
    </p>
    <div class="hero-actions reveal reveal-delay-3">
      <button class="btn btn-primary btn-lg" onclick="openModal('register')">
        {{ $cms['hero']['cta_primary'] ?? 'Coba Gratis' }}
      </button>
      @if(!empty($cms['hero']['cta_secondary']))
      <button class="btn btn-secondary btn-lg" onclick="scrollToSection('video')">
        {{ $cms['hero']['cta_secondary'] }}
      </button>
      @endif
    </div>
  </div>
</section>
