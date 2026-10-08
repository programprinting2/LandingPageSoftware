<section class="workflow-section" id="cara-kerja">
  <div class="container">
    <div class="workflow-header reveal">
      <h2 class="section-title">{{ $cms['workflow']['title'] ?? 'Alur Kerja Mudah dan Simpel' }}</h2>
    </div>
    <div class="workflow-grid">
      @foreach(($cms['workflow']['steps'] ?? []) as $i => $step)
      <div class="workflow-card reveal reveal-delay-{{ ($i % 4) + 1 }}">
        <div class="workflow-step-num">{{ $i + 1 }}</div>
        <h3>{{ $step['title'] }}</h3>
        <p>{{ $step['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>
