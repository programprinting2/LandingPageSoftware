@php
  $site = $hub['site'] ?? [];
@endphp
<header class="hub-header">
  <div class="container hub-header-inner">
    <a href="{{ route('landing') }}" class="hub-brand-wrap">
      <span class="hub-brand">{{ $site['brand'] ?? 'Softsale' }}</span>
      @if(!empty($site['tagline']))
        <span class="hub-brand-tag">{{ $site['tagline'] }}</span>
      @endif
    </a>
    <nav class="hub-nav">
      <button type="button" class="hub-nav-btn hub-nav-btn--ghost" onclick="openModal('login')">{{ $site['nav_login'] ?? 'Masuk' }}</button>
      <button type="button" class="hub-nav-btn hub-nav-btn--primary" onclick="openModal('register')">{{ $site['nav_register'] ?? 'Daftar' }}</button>
    </nav>
  </div>
</header>
