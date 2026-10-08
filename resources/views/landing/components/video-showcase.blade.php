<section class="video-section" id="video">
  <div class="container">
    <div class="video-wrapper" @if(!empty($cms['video']['url'])) onclick="window.open('{{ $cms['video']['url'] }}', '_blank')" @endif>
      <div class="video-wrapper-inner">
        <div class="video-play-btn">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
        </div>
      </div>
    </div>
  </div>
</section>
