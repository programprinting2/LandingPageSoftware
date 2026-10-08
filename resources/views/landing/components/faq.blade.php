<section class="faq-section" id="faq">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <h2 class="section-title">{{ $cms['faq']['title'] ?? 'Pertanyaan Umum' }}</h2>
      @if(!empty($cms['faq']['subtitle']))
      <p class="section-sub">{{ $cms['faq']['subtitle'] }}</p>
      @endif
    </div>
    <div class="faq-list" id="faqList">
      @foreach(($cms['faq']['items'] ?? []) as $i => $item)
      <div class="faq-item reveal reveal-delay-{{ ($i % 3) + 1 }}">
        <button class="faq-question" onclick="toggleFaq(this)" aria-expanded="false">
          <span>{{ $item['question'] }}</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">{{ $item['answer'] }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
