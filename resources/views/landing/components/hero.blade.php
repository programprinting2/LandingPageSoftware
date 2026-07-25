<!-- HERO -->
<section class="hero" id="hero">
  <div class="hero-glow"></div>
  <div class="hero-grid"></div>
  <div class="hero-particles" id="particles"></div>
  <div class="hero-content">
    <div class="badge-label reveal" style="background:rgba(56,126,245,0.1); border:1px solid rgba(56,126,245,0.2); color:var(--accent-1);">
      {{ $cms['hero']['badge'] ?? 'Solusi Bisnis Digital Terdepan' }}
    </div>
    <h1 class="reveal reveal-delay-1">{{ $cms['hero']['title'] ?? 'Kelola Bisnis Anda' }} <span class="gradient-text">{{ $cms['hero']['title_highlight'] ?? 'Tanpa Batas' }}</span></h1>
    <p class="hero-sub reveal reveal-delay-2">
      {{ $cms['hero']['subtitle'] ?? '' }}
    </p>
    <div class="hero-actions reveal reveal-delay-3">
      <button class="btn btn-primary btn-lg" onclick="openModal('register')">
        {{ $cms['hero']['cta_primary'] ?? 'Mulai Gratis 14 Hari' }}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
      @if(!empty($cms['hero']['cta_secondary']))
      <button class="btn btn-secondary btn-lg">{{ $cms['hero']['cta_secondary'] }}</button>
      @endif
    </div>
    <div class="hero-stats reveal reveal-delay-4">
      @foreach(($cms['hero']['stats'] ?? []) as $stat)
      <div>
        @if(!empty($stat['animate']))
        <div class="hero-stat-n" data-count="{{ $stat['value'] }}">0</div>
        @else
        <div class="hero-stat-n">{{ $stat['value'] }}</div>
        @endif
        <div class="hero-stat-l">{{ $stat['label'] }}</div>
      </div>
      @endforeach
    </div>
  </div>
</section>
