@php
  $footer = $hub['footer'] ?? [];
  $productsTitle = $footer['products_title'] ?? 'Produk';
  $accountTitle = $footer['account_title'] ?? 'Akun';
  $catalogList = $catalogItems ?? [];
@endphp
<footer class="hub-footer">
  <div class="container hub-footer-grid">
    <div class="hub-footer-brand">
      <div class="hub-footer-logo">{{ $hub['site']['brand'] ?? 'Softsale' }}</div>
      <p class="hub-footer-desc">{{ $footer['description'] ?? '' }}</p>
    </div>
    <div>
      <div class="hub-footer-col-title">{{ $productsTitle }}</div>
      @forelse($catalogList as $item)
        @if(empty($item['is_dummy']) && !empty($item['url']))
          <a class="hub-footer-link" href="{{ $item['url'] }}">{{ $item['name'] }}</a>
        @else
          <span class="hub-footer-link hub-footer-muted">{{ $item['name'] }} · segera</span>
        @endif
      @empty
        @forelse($applications ?? [] as $app)
          <a class="hub-footer-link" href="{{ route('landing.application', $app->slug) }}">{{ $app->name }}</a>
        @empty
          <span class="hub-footer-link hub-footer-muted">—</span>
        @endforelse
      @endforelse
    </div>
    <div>
      @php($clientLoggedIn = ! empty($landingData['initialClient']))
      <div class="hub-footer-col-title">{{ $accountTitle }}</div>
      <span class="hub-footer-link" data-auth-guest @if($clientLoggedIn) hidden @endif onclick="openModal('register')">Daftar</span>
      <span class="hub-footer-link" data-auth-guest @if($clientLoggedIn) hidden @endif onclick="openModal('login')">Masuk</span>
      <span class="hub-footer-link" data-auth-user @if(! $clientLoggedIn) hidden @endif onclick="goToClientDashboard()">Dashboard</span>
    </div>
  </div>
  <div class="container hub-footer-bottom">{{ $footer['copyright'] ?? '' }}</div>
</footer>
