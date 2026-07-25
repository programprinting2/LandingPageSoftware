<!-- TENTANG -->
<section id="tentang" class="about-section">
  <div class="container">
    <div class="about-grid">
      <div class="about-left reveal">
        <div class="badge-label" style="background:rgba(245,166,35,0.08); color:var(--amber);">{{ $cms['about']['badge'] ?? 'Tentang Kami' }}</div>
        <h2 class="section-title" style="font-size:34px;">{{ $cms['about']['title'] ?? 'Dibangun oleh praktisi,' }}<br>{{ $cms['about']['title_break'] ?? 'untuk praktisi bisnis' }}</h2>
        @foreach(($cms['about']['paragraphs'] ?? []) as $paragraph)
        <p>{{ $paragraph }}</p>
        @endforeach
        <div class="about-stats">
          @foreach(($cms['about']['stats'] ?? []) as $stat)
          <div>
            @if(!empty($stat['animate']))
            <div class="about-stat-n" data-count="{{ $stat['value'] }}">0</div>
            @else
            <div class="about-stat-n">{{ $stat['value'] }}</div>
            @endif
            <div class="about-stat-l">{{ $stat['label'] }}</div>
          </div>
          @endforeach
        </div>
      </div>
      <div class="about-values" id="aboutValues"></div>
    </div>
  </div>
</section>
