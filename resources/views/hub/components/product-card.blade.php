@php
  $isDummy = !empty($item['is_dummy']);
  $accent = $item['accent'] ?? 'orange';
  $featured = !empty($item['featured']) && !$isDummy;
  $tagClass = 'hub-card--accent-' . $accent;
@endphp

@if($isDummy)
  <article class="hub-card hub-card--dummy {{ $tagClass }}">
    @if(!empty($item['badge']))
      <span class="hub-card-badge hub-card-badge--muted">{{ $item['badge'] }}</span>
    @endif
    <div class="hub-card-top">
      <div class="hub-card-icon" aria-hidden="true">{{ strtoupper(substr($item['name'], 0, 1)) }}</div>
      <span class="hub-card-cat">{{ $item['category'] ?? '' }}</span>
    </div>
    <h3>{{ $item['name'] }}</h3>
    <p>{{ $item['description'] }}</p>
    <span class="hub-card-link hub-card-link--muted">Coming soon</span>
  </article>
@else
  <a href="{{ $item['url'] }}" class="hub-card {{ $tagClass }}{{ $featured ? ' hub-card--featured' : '' }}">
    @if(!empty($item['badge']))
      <span class="hub-card-badge">{{ $item['badge'] }}</span>
    @endif
    <div class="hub-card-top">
      <div class="hub-card-icon" aria-hidden="true">{{ strtoupper(substr($item['name'], 0, 1)) }}</div>
      <span class="hub-card-cat">{{ $item['category'] ?? '' }}</span>
    </div>
    <h3>{{ $item['name'] }}</h3>
    <p>{{ $item['description'] }}</p>
    <span class="hub-card-link">Jelajahi produk →</span>
  </a>
@endif
