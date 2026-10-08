<section class="hero" id="hero">
  <div class="container hero-inner">
    <div class="hero-text">
      <h1 class="reveal">
        {{ $cms['hero']['title'] ?? 'Solusi' }}
        <span class="text-accent">{{ $cms['hero']['title_highlight'] ?? 'SATSET' }}</span>
        {{ $cms['hero']['title_suffix'] ?? 'cetak DTF! Proses PNG siap cetak hanya dengan' }}
        <span class="text-accent">{{ $cms['hero']['title_highlight_2'] ?? '1x klik' }}</span>.
      </h1>
      <p class="hero-sub reveal reveal-delay-1">
        {{ $cms['hero']['subtitle'] ?? 'Tinggalkan proses bikin spot color & white mask manual.' }}
      </p>
      <div class="hero-actions reveal reveal-delay-2">
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
    <div class="hero-visual reveal reveal-delay-2">
      <img src="{{ asset('images/hero image.png') }}" alt="Quick Spot Color — Software DTF Printing">
    </div>
  </div>
</section>
