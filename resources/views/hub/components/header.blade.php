@php
  $site = $hub['site'] ?? [];
  $clientLoggedIn = ! empty($landingData['initialClient']);
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
      <div class="hub-nav-auth" data-auth-guest @if($clientLoggedIn) hidden @endif>
        <button type="button" class="hub-nav-btn hub-nav-btn--ghost" onclick="openModal('login')">{{ $site['nav_login'] ?? 'Masuk' }}</button>
        <button type="button" class="hub-nav-btn hub-nav-btn--primary" onclick="openModal('register')">{{ $site['nav_register'] ?? 'Daftar' }}</button>
      </div>
      <div class="hub-nav-auth" data-auth-user @if(! $clientLoggedIn) hidden @endif>
        <button type="button" class="hub-nav-btn hub-nav-btn--primary" onclick="goToClientDashboard()">Dashboard</button>
      </div>
    </nav>
  </div>
</header>
