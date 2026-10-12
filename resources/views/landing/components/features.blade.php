<section class="features-split-section" id="fitur">
  <div class="features-split-header">
    <h2>{{ $cms['features']['title'] ?? 'Dirancang untuk Produktivitas Cetak' }}</h2>
  </div>
  <div class="features-split-body">
    <div class="features-split-bg">
      <div class="features-split-bg-left"></div>
      <div class="features-split-bg-right"></div>
    </div>
    <div class="container features-split-rows">
      @foreach(($cms['features']['items'] ?? []) as $i => $feature)
      <div class="features-split-row">
        @if($i % 2 === 0)
        <div class="feature-text feature-text--navy reveal">
          <h3>{{ $feature['title'] }}</h3>
          @if(!empty($feature['desc']))
          <p>{{ $feature['desc'] }}</p>
          @endif
        </div>
        <div class="feature-media reveal reveal-delay-1">
          <div class="features-split-placeholder">
            @if(!empty($feature['image']))
            <img src="{{ $feature['image'] }}" alt="{{ $feature['title'] }}">
            @endif
          </div>
        </div>
        @else
        <div class="feature-media reveal">
          <div class="features-split-placeholder">
            @if(!empty($feature['image']))
            <img src="{{ $feature['image'] }}" alt="{{ $feature['title'] }}">
            @endif
          </div>
        </div>
        <div class="feature-text feature-text--navy feature-text--boxed reveal reveal-delay-1">
          <h3>{{ $feature['title'] }}</h3>
          @if(!empty($feature['desc']))
          <p>{{ $feature['desc'] }}</p>
          @endif
        </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>
</section>
